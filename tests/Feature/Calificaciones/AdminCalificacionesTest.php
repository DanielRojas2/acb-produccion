<?php

namespace Tests\Feature\Calificaciones;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\CalificacionHistorial;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class AdminCalificacionesTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_puede_ver_detalle_de_calificaciones_de_una_presentacion(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 88.5,
            'estado' => CalificacionEstadoEnum::EMITIDO,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.calificaciones.presentacion', $presentacion));

        $response->assertOk();
        $response->assertSee('Detalle de Calificaciones');
        $response->assertSee('88.50');
        $response->assertSee($jurado->nombre);
    }

    public function test_admin_puede_ver_historial_de_auditoria_de_una_calificacion(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);
        $presentacion = $this->presentacionPreseleccion();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $calificacion = Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 95.0,
            'estado' => CalificacionEstadoEnum::CORREGIDO,
        ]);

        CalificacionHistorial::create([
            'id_calificacion' => $calificacion->id,
            'id_usuario' => $admin->id,
            'datos_anteriores' => ['nota_total' => 85.0, 'detalles' => []],
            'datos_nuevos' => ['nota_total' => 95.0, 'detalles' => []],
            'motivo' => 'Corrección por revisión técnica',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.calificaciones.historial', $calificacion));

        $response->assertOk();
        $response->assertSee('Historial de Correcciones y Auditoría');
        $response->assertSee('Corrección por revisión técnica');
    }

    public function test_jurado_no_puede_acceder_al_panel_admin_de_calificaciones(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();

        $response = $this->actingAs($jurado)->get(route('admin.calificaciones.presentacion', $presentacion));

        $response->assertForbidden();
    }
}
