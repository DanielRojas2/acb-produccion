<?php

namespace Tests\Feature\Importaciones;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Importaciones\Services\ImportarInscripcionesService;
use App\Modules\Importaciones\ValueObjects\ResultadoImportacion;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportacionJsonTest extends TestCase
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

    private function importar(string $contenido): ResultadoImportacion
    {
        return app(ImportarInscripcionesService::class)
            ->importar(UploadedFile::fake()->createWithContent('datos.json', $contenido));
    }

    #[Test]
    public function importa_json_con_integrantes_anidados(): void
    {
        $resultado = $this->importar(json_encode(['filas' => [[
            'categoria' => 'Autóctono',
            'departamento' => 'Potosí',
            'fase' => 'PRESELECCION',
            'fecha_inscripcion' => '2026-09-11',
            'nombre_presentacion' => 'Autóctono Pampa',
            'integrantes' => [
                ['nombre' => 'Julio', 'paterno' => 'Apaza', 'materno' => 'Choque'],
                ['nombre' => 'Beatriz', 'paterno' => 'Apaza', 'materno' => 'Flores'],
            ],
        ]]]));

        $this->assertTrue($resultado->exitoso, $resultado->errorGeneral ?? '');
        $this->assertSame(1, Inscripcion::count());
        $this->assertSame(2, Participante::count());
        $this->assertSame(1, Presentacion::count());
        $this->assertSame('Autóctono Pampa', Inscripcion::first()->presentacion->nombre_presentacion);

        $this->assertSame(7, Categoria::where('categoria', 'Autóctono')->first()->tiempo_presentacion);
        $this->assertSame(7, Inscripcion::first()->presentacion->tiempo_maximo);
    }

    #[Test]
    public function importa_json_plano_con_columnas_integrante(): void
    {
        $resultado = $this->importar(json_encode([[
            'categoria' => 'Ñusta',
            'departamento' => 'Tarija',
            'fase' => 'PRESELECCION',
            'integrante_1_nombre' => 'Flor',
            'integrante_1_paterno' => 'Zeballos',
        ]]));

        $this->assertTrue($resultado->exitoso, $resultado->errorGeneral ?? '');
        $this->assertSame(1, Inscripcion::count());
        $this->assertSame('Flor Zeballos', Inscripcion::first()->presentacion->nombre_presentacion);
    }

    #[Test]
    public function json_malformado_no_persiste_nada(): void
    {
        $resultado = $this->importar('{esto no es un json');

        $this->assertFalse($resultado->exitoso);
        $this->assertNotNull($resultado->errorGeneral);
        $this->assertSame(0, Inscripcion::count());
        $this->assertSame(0, Presentacion::count());
    }

    #[Test]
    public function json_con_estructura_desconocida_no_persiste_nada(): void
    {
        $resultado = $this->importar(json_encode(['otros' => []]));

        $this->assertFalse($resultado->exitoso);
        $this->assertStringContainsString('Estructura JSON no reconocida', $resultado->errorGeneral);
        $this->assertSame(0, Inscripcion::count());
    }
}
