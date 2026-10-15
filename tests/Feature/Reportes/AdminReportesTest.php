<?php

namespace Tests\Feature\Reportes;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Fases\Models\Fase;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class AdminReportesTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_jurado_no_puede_acceder_al_centro_de_reportes(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $response = $this->actingAs($jurado)->get(route('admin.reportes.index'));
        $response->assertForbidden();
    }

    public function test_admin_puede_ver_centro_de_reportes(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();

        $response = $this->actingAs($admin)->get(route('admin.reportes.index'));
        $response->assertOk();
        $response->assertSee('Centro de Reportes y Actas Oficiales');
        $response->assertSee('Acta Notarial de Escrutinio');
    }

    public function test_admin_puede_generar_vista_de_resultados_oficiales(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::FINALIZADO;
        $presentacion->nota_final = 95.5;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 95.5,
            'estado' => CalificacionEstadoEnum::EMITIDO,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $fase = $presentacion->inscripcion->fase;

        $response = $this->actingAs($admin)->get(route('admin.reportes.resultados', [
            'fase_id' => $fase->id,
            'categoria_id' => $categoria->id,
        ]));

        $response->assertOk();
        $response->assertSee('Resultados Oficiales: '.$categoria->categoria);
        $response->assertSee($presentacion->nombreMostrado());
        $response->assertSee('95.50');
    }

    public function test_admin_puede_generar_acta_notarial_print_ready(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->nota_final = 88.0;
        $presentacion->save();

        $categoria = $presentacion->inscripcion->categoria;
        $fase = $presentacion->inscripcion->fase;

        $response = $this->actingAs($admin)->get(route('admin.reportes.acta', [
            'fase_id' => $fase->id,
            'categoria_id' => $categoria->id,
        ]));

        $response->assertOk();
        $response->assertSee('ACTA DE ESCRUTINIO Y CALIFICACIÓN FINAL');
        $response->assertSee('Notario de Fe Pública');
        $response->assertSee('Presidente del Jurado');
        $response->assertSee($presentacion->nombreMostrado());
    }

    public function test_admin_puede_ver_sabana_detallada(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();
        $categoria = $presentacion->inscripcion->categoria;
        $fase = $presentacion->inscripcion->fase;

        $response = $this->actingAs($admin)->get(route('admin.reportes.sabana', [
            'fase_id' => $fase->id,
            'categoria_id' => $categoria->id,
        ]));

        $response->assertOk();
        $response->assertSee('Sábana de Calificación por Parámetros');
        $response->assertSee($categoria->categoria);
    }

    public function test_admin_puede_exportar_csv_de_resultados_y_sabana(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();
        $categoria = $presentacion->inscripcion->categoria;
        $fase = $presentacion->inscripcion->fase;

        // Exportar resultados CSV
        $responseResultados = $this->actingAs($admin)->get(route('admin.reportes.exportar.resultados', [
            'fase_id' => $fase->id,
            'categoria_id' => $categoria->id,
        ]));
        $responseResultados->assertOk();
        $responseResultados->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Exportar sábana CSV
        $responseSabana = $this->actingAs($admin)->get(route('admin.reportes.exportar.sabana', [
            'fase_id' => $fase->id,
            'categoria_id' => $categoria->id,
        ]));
        $responseSabana->assertOk();
        $responseSabana->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
