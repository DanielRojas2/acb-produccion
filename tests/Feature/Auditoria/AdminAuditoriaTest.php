<?php

namespace Tests\Feature\Auditoria;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\CalificacionHistorial;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class AdminAuditoriaTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_jurado_no_puede_acceder_a_la_seccion_de_auditoria(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $response = $this->actingAs($jurado)->get(route('admin.auditoria.index'));
        $response->assertForbidden();
    }

    public function test_admin_puede_listar_y_filtrar_registros_de_auditoria(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::FINALIZADO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $calificacion = Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 85.0,
            'estado' => CalificacionEstadoEnum::CORREGIDO,
        ]);

        $historial = CalificacionHistorial::create([
            'id_calificacion' => $calificacion->id,
            'id_usuario' => $jurado->id,
            'datos_anteriores' => ['nota_total' => 75.0, 'detalles' => []],
            'datos_nuevos' => ['nota_total' => 85.0, 'detalles' => []],
            'motivo' => 'Ajuste de puntos por deliberación oficial',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.auditoria.index'));
        $response->assertOk();
        $response->assertSee('Auditoría y Trazabilidad de Calificaciones');
        $response->assertSee('Ajuste de puntos por deliberación oficial');
        $response->assertSee($jurado->nombre);

        // Filtrado por categoría
        $responseFiltro = $this->actingAs($admin)->get(route('admin.auditoria.index', [
            'categoria_id' => $presentacion->inscripcion->id_categoria,
        ]));
        $responseFiltro->assertOk();
        $responseFiltro->assertSee('Ajuste de puntos por deliberación oficial');
    }

    public function test_admin_puede_ver_detalle_comparativo_de_un_historial(): void
    {
        $admin = Usuario::factory()->create(['rol' => RolEnum::ADMIN]);

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::FINALIZADO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $param1 = $categoria->categoriaParametros->first();

        $calificacion = Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 90.0,
            'estado' => CalificacionEstadoEnum::CORREGIDO,
        ]);

        $historial = CalificacionHistorial::create([
            'id_calificacion' => $calificacion->id,
            'id_usuario' => $jurado->id,
            'datos_anteriores' => [
                'nota_total' => 80.0,
                'detalles' => [
                    ['id_parametro' => $param1->id_parametro, 'valor' => 20.0],
                ],
            ],
            'datos_nuevos' => [
                'nota_total' => 90.0,
                'detalles' => [
                    ['id_parametro' => $param1->id_parametro, 'valor' => 30.0],
                ],
            ],
            'motivo' => 'Corrección detallada por afinación vocal',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.auditoria.show', $historial));
        $response->assertOk();
        $response->assertSee('Detalle de Auditoría');
        $response->assertSee('Corrección detallada por afinación vocal');
        $response->assertSee('80.00');
        $response->assertSee('90.00');
        $response->assertSee('+10.00');
    }
}
