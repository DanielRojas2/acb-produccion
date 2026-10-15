<?php

namespace Tests\Feature\Importaciones;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Importaciones\Services\ImportarInscripcionesService;
use App\Modules\Importaciones\ValueObjects\ResultadoImportacion;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportacionCsvTest extends TestCase
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

    private function importar(string $contenido, string $nombre = 'datos.csv'): ResultadoImportacion
    {
        return app(ImportarInscripcionesService::class)
            ->importar(UploadedFile::fake()->createWithContent($nombre, $contenido));
    }

    private function contarPersistencia(): array
    {
        return [
            Inscripcion::count(),
            Inscripcion::has('presentacion')->count(),
            Participante::count(),
            Presentacion::count(),
        ];
    }

    #[Test]
    public function importa_una_inscripcion_completa(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;fecha_inscripcion;integrante_1_nombre;integrante_1_paterno;integrante_1_materno\n"
            ."Solista vocal (con o sin acompañamiento);Chuquisaca;PRESELECCION;2026-09-11;Camila;Mamani;Rivera\n"
        );

        $this->assertTrue($resultado->exitoso, $resultado->errorGeneral ?? '');
        $this->assertSame([1, 1, 1, 1], $this->contarPersistencia());

        $categoria = Categoria::where('categoria', 'Solista vocal (con o sin acompañamiento)')->first();
        $inscripcion = Inscripcion::first();

        $this->assertSame($categoria->id, $inscripcion->id_categoria);
        $this->assertSame(1, $inscripcion->participantes()->count());

        $presentacion = $inscripcion->presentacion;
        $this->assertSame(PresentacionEstadoEnum::POR_PARTICIPAR, $presentacion->estado);
        $this->assertSame($categoria->tiempo_presentacion, $presentacion->tiempo_maximo);
        $this->assertSame('Camila Mamani Rivera', $presentacion->nombre_presentacion);
    }

    #[Test]
    public function importa_un_duo_con_nombre_de_presentacion(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;nombre_presentacion;integrante_1_nombre;integrante_1_paterno;integrante_2_nombre;integrante_2_paterno\n"
            ."Dúo vocal instrumental;La Paz;PRESELECCION;Dúo Andino;Juan;Pérez;María;López\n"
        );

        $this->assertTrue($resultado->exitoso);
        $this->assertSame([1, 1, 2, 1], $this->contarPersistencia());

        $inscripcion = Inscripcion::first();
        $this->assertSame(2, $inscripcion->participantes()->count());
        $this->assertSame('Dúo Andino', $inscripcion->presentacion->nombre_presentacion);
    }

    #[Test]
    public function etapa_final_no_exige_departamento(): void
    {
        $resultado = $this->importar(
            "categoria;fase;nombre_presentacion;integrante_1_nombre\n"
            ."Solista vocal (con o sin acompañamiento);FINAL;Final Nacional;Camila\n"
        );

        $this->assertTrue($resultado->exitoso, ($resultado->errorGeneral ?? '').collect($resultado->errores)->implode('mensaje', ', '));
        $this->assertNull(Inscripcion::first()->id_departamento);
    }

    #[Test]
    public function no_persiste_nada_si_la_categoria_no_existe(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;integrante_1_nombre\n"
            ."Categoría Inexistente;Oruro;PRESELECCION;Ana\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertNotEmpty($resultado->errores);
        $this->assertSame([0, 0, 0, 0], $this->contarPersistencia());
    }

    #[Test]
    public function no_persiste_nada_si_falta_departamento_en_preseleccion(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;integrante_1_nombre\n"
            ."Solista vocal (con o sin acompañamiento);;PRESELECCION;Ana\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertSame('En preselección es obligatorio indicar el departamento.', $resultado->errores[0]['mensaje']);
        $this->assertSame([0, 0, 0, 0], $this->contarPersistencia());
    }

    #[Test]
    public function rechaza_superar_el_maximo_de_integrantes(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;nombre_presentacion;integrante_1_nombre;integrante_2_nombre\n"
            ."Ñusta;Oruro;PRESELECCION;;Ana;Luis\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertStringContainsString('admite máximo 1 integrante', $resultado->errores[0]['mensaje']);
        $this->assertSame([0, 0, 0, 0], $this->contarPersistencia());
    }

    #[Test]
    public function rechaza_una_fila_sin_integrantes(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;nombre_presentacion\n"
            ."Solista vocal (con o sin acompañamiento);Oruro;PRESELECCION;\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertSame('Debe indicar al menos un integrante.', $resultado->errores[0]['mensaje']);
        $this->assertSame([0, 0, 0, 0], $this->contarPersistencia());
    }

    #[Test]
    public function rechaza_integrantes_repetidos_dentro_de_la_misma_fila(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;nombre_presentacion;integrante_1_nombre;integrante_2_nombre\n"
            ."Dúo vocal instrumental;Oruro;PRESELECCION;XX;Ana;Ana\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertStringContainsString('integrantes repetidos', $resultado->errores[0]['mensaje']);
    }

    #[Test]
    public function rechaza_inscripciones_repetidas_entre_filas(): void
    {
        $resultado = $this->importar(
            "categoria;departamento;fase;nombre_presentacion;integrante_1_nombre\n"
            ."Solista vocal (con o sin acompañamiento);Oruro;PRESELECCION;A;Ana\n"
            ."Solista vocal (con o sin acompañamiento);Oruro;PRESELECCION;A;Ana\n"
        );

        $this->assertFalse($resultado->exitoso);
        $this->assertCount(1, $resultado->errores);
        $this->assertSame(2, $resultado->errores[0]['fila']);
        $this->assertSame([0, 0, 0, 0], $this->contarPersistencia());
    }

    #[Test]
    public function reutiliza_participantes_ya_existentes_en_otras_inscripciones(): void
    {
        $this->importar(
            "categoria;departamento;fase;integrante_1_nombre;integrante_1_paterno\n"
            ."Solista vocal (con o sin acompañamiento);Oruro;PRESELECCION;Ana;García\n"
        );

        $resultado = $this->importar(
            "categoria;departamento;fase;integrante_1_nombre;integrante_1_paterno\n"
            ."Ñusta;Oruro;PRESELECCION;Ana;García\n"
        );

        $this->assertTrue($resultado->exitoso);
        $this->assertSame(0, $resultado->participantesCreados);
        $this->assertSame(1, Participante::count());
        $this->assertSame(2, Inscripcion::count());
        $this->assertSame(2, Presentacion::count());
    }

    #[Test]
    public function un_jurado_no_puede_importar(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $this->actingAs($jurado)
            ->post(route('admin.importaciones.importar'), [
                'archivo' => UploadedFile::fake()->createWithContent('datos.csv', "categoria;departamento;fase;integrante_1_nombre\n"),
            ])
            ->assertForbidden();
    }

    #[Test]
    public function un_admin_puede_importar_via_http(): void
    {
        $admin = Usuario::factory()->administrador()->create();

        $respuesta = $this->actingAs($admin)
            ->post(route('admin.importaciones.importar'), [
                'archivo' => UploadedFile::fake()->createWithContent(
                    'datos.csv',
                    "categoria;departamento;fase;integrante_1_nombre\n"
                    ."Estudiantina;Potosí;PRESELECCION;Luis\n"
                ),
            ]);

        $respuesta->assertRedirect(route('admin.importaciones.index'));

        $this->assertSame(1, Inscripcion::count());
        $this->assertSame(1, Presentacion::count());
    }
}
