<?php

namespace Tests\Feature\Presentaciones;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Events\CronometroActualizado;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class CronometroTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartamentoSeeder::class,
            FaseSeeder::class,
            CategoriaParametrosSeeder::class,
        ]);
    }

    #[Test]
    public function un_admin_con_control_de_tiempo_puede_iniciar_y_detener_el_cronometro(): void
    {
        $admin = Usuario::factory()->administrador()->create(['puede_controlar_tiempo' => true]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($admin)
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertRedirect();

        $presentacion->refresh();
        $this->assertNotNull($presentacion->cronometro_iniciado_at);

        $this->actingAs($admin)
            ->post(route('presentaciones.cronometro.detener', $presentacion))
            ->assertRedirect();

        $presentacion->refresh();
        $this->assertNotNull($presentacion->cronometro_finalizado_at);
    }

    #[Test]
    public function un_admin_sin_control_de_tiempo_no_puede_manejar_el_cronometro(): void
    {
        $admin = Usuario::factory()->administrador()->create(['puede_controlar_tiempo' => false]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($admin)
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertForbidden();
    }

    #[Test]
    public function un_jurado_controlador_puede_manejar_el_cronometro_de_su_presentacion(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO, 'puede_controlar_tiempo' => true]);
        $presentacion = $this->presentacionPreseleccion();

        JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
            'es_controlador' => true,
        ]);

        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($jurado)
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertRedirect();

        $this->assertNotNull($presentacion->fresh()->cronometro_iniciado_at);
    }

    #[Test]
    public function un_jurado_no_controlador_no_puede_manejar_el_cronometro(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO, 'puede_controlar_tiempo' => false]);
        $presentacion = $this->presentacionPreseleccion();

        JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
            'es_controlador' => false,
        ]);

        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($jurado)
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertForbidden();
    }

    #[Test]
    public function no_se_puede_iniciar_si_el_cronometro_ya_fue_utilizado(): void
    {
        $admin = Usuario::factory()->administrador()->create(['puede_controlar_tiempo' => true]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill([
            'estado' => PresentacionEstadoEnum::PARTICIPANDO,
            'cronometro_iniciado_at' => now()->subMinutes(2),
            'cronometro_finalizado_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->from(route('admin.presentaciones.index'))
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertRedirect(route('admin.presentaciones.index'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function iniciar_el_cronometro_emite_el_evento_websocket(): void
    {
        Event::fake([CronometroActualizado::class]);

        $admin = Usuario::factory()->administrador()->create(['puede_controlar_tiempo' => true]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($admin)
            ->post(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertRedirect();

        Event::assertDispatched(CronometroActualizado::class);
    }

    #[Test]
    public function peticion_ajax_iniciar_y_detener_retorna_json_con_timestamps_del_servidor(): void
    {
        $admin = Usuario::factory()->administrador()->create(['puede_controlar_tiempo' => true]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        // Iniciar vía JSON
        $respuestaIniciar = $this->actingAs($admin)
            ->postJson(route('presentaciones.cronometro.iniciar', $presentacion))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'cronometro' => [
                    'presentacion_id',
                    'corriendo',
                    'tiempo_maximo',
                    'cronometro_iniciado_at',
                    'cronometro_iniciado_at_ms',
                    'server_now_ms',
                    'segundos_restantes',
                ],
                'estado',
                'estado_label',
            ]);

        $this->assertTrue($respuestaIniciar->json('cronometro.corriendo'));
        $this->assertNotNull($respuestaIniciar->json('cronometro.server_now_ms'));
        $this->assertNotNull($respuestaIniciar->json('cronometro.cronometro_iniciado_at_ms'));

        $presentacion->refresh();

        // Detener vía JSON
        $respuestaDetener = $this->actingAs($admin)
            ->postJson(route('presentaciones.cronometro.detener', $presentacion))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'cronometro' => [
                    'presentacion_id',
                    'corriendo',
                    'cronometro_finalizado_at',
                    'cronometro_finalizado_at_ms',
                    'server_now_ms',
                ],
            ]);

        $this->assertFalse($respuestaDetener->json('cronometro.corriendo'));
        $this->assertNotNull($respuestaDetener->json('cronometro.cronometro_finalizado_at_ms'));
    }
}