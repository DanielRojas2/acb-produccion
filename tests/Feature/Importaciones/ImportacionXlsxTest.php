<?php

namespace Tests\Feature\Importaciones;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Importaciones\Readers\FabricaLectores;
use App\Modules\Importaciones\Services\ImportarInscripcionesService;
use App\Modules\Importaciones\ValueObjects\ResultadoImportacion;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use ZipArchive;

class ImportacionXlsxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartamentoSeeder::class,
            FaseSeeder::class,
            CategoriaParametrosSeeder::class,
        ]);
    }

    private function importar(): ResultadoImportacion
    {
        $ruta = $this->generarXlsx();

        return app(ImportarInscripcionesService::class)->importar($ruta);
    }

    private function generarXlsx(): string
    {
        $filas = [
            ['categoria', 'departamento', 'fase', 'fecha_presentacion', 'hora_presentacion', 'nombre_presentacion', 'integrante_1_nombre', 'integrante_1_paterno', 'integrante_1_materno', 'integrante_2_nombre', 'integrante_2_paterno'],
            ['Trío vocal instrumental', 'Oruro', 'PRESELECCION', '2026-10-10', '21:00', 'Trío Oruro', 'Carlos', 'Choque', 'Soria', 'Rosa', 'Flores'],
            ['Solista vocal infantil (con o sin acompañamiento)', 'Santa Cruz', 'PRESELECCION', '2026-10-07', '18:30', '', 'Lucía', 'Rojas', 'Cruz', '', ''],
        ];

        $compartidas = [];
        $indices = [];
        $filasXml = [];

        foreach ($filas as $i => $fila) {
            $celdas = '';

            foreach ($fila as $j => $valor) {
                $ref = $this->columnaLetra($j).($i + 1);

                if (! isset($indices[$valor])) {
                    $indices[$valor] = count($compartidas);
                    $compartidas[] = $valor;
                }

                $celdas .= '<c r="'.$ref.'" t="s"><v>'.$indices[$valor].'</v></c>';
            }

            $filasXml[] = '<row r="'.($i + 1).'">'.$celdas.'</row>';
        }

        $sstItems = '';

        foreach ($compartidas as $texto) {
            $sstItems .= '<si><t>'.htmlspecialchars($texto, ENT_XML1).'</t></si>';
        }

        $hoja = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.implode('', $filasXml).'</sheetData></worksheet>';

        $libro = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Hoja1" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $relaciones = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';

        $contenidoTipos = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .'</Types>';

        $ruta = sys_get_temp_dir().'/prueba_importacion.xlsx';
        $zip = new ZipArchive;
        $zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contenidoTipos);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $libro);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $relaciones);
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($compartidas).'" uniqueCount="'.count($compartidas).'">'.$sstItems.'</sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $hoja);
        $zip->close();

        return $ruta;
    }

    private function columnaLetra(int $indice): string
    {
        $letras = '';
        $n = $indice + 1;

        while ($n > 0) {
            $n--;
            $letras = chr(65 + ($n % 26)).$letras;
            $n = intdiv($n, 26);
        }

        return $letras;
    }

    #[Test]
    public function lee_y_importa_un_xlsx(): void
    {
        $ruta = $this->generarXlsx();

        $filas = app(FabricaLectores::class)->crear('xlsx')->leer($ruta);

        $this->assertCount(2, $filas);
        $this->assertSame('Trío vocal instrumental', $filas[0]['categoria']);
        $this->assertSame('Trío Oruro', $filas[0]['nombre_presentacion']);
        $this->assertCount(2, $filas[0]['integrantes']);

        $resultado = $this->importar();

        $this->assertTrue($resultado->exitoso, $resultado->errorGeneral ?? '');
        $this->assertSame(2, Inscripcion::count());
        $this->assertSame(3, Participante::count());
        $this->assertSame(2, Presentacion::count());

        $trío = Inscripcion::where('id_categoria', Categoria::where('categoria', 'Trío vocal instrumental')->first()->id)->first();
        $this->assertSame('Trío Oruro', $trío->presentacion->nombre_presentacion);
        $this->assertSame(2, $trío->participantes()->count());
    }

    #[Test]
    public function sube_un_xlsx_desde_ui_mantiene_nombre_de_presentacion(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $contenido = file_get_contents($this->generarXlsx());

        $this->actingAs($admin)
            ->post(route('admin.importaciones.importar'), [
                'archivo' => UploadedFile::fake()->createWithContent('inscripciones.xlsx', $contenido),
            ])
            ->assertRedirect(route('admin.importaciones.index'));

        $this->assertSame(2, Inscripcion::count());
        $this->assertSame('Lucía Rojas Cruz', Inscripcion::has('presentacion')->whereHas('categoria', fn ($q) => $q->where('categoria', 'like', 'Solista vocal infantil%'))->first()->presentacion->nombre_presentacion);
    }
}
