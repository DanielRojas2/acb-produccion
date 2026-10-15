<?php

namespace Tests\Feature\Presentaciones;

use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class PresentacionesEstadosTest extends TestCase
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
    public function un_admin_pasa_la_presentacion_al_escenario(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($admin)
            ->from(route('admin.presentaciones.index'))
            ->post(route('admin.presentaciones.transicion', $presentacion), [
                'estado' => PresentacionEstadoEnum::EN_ESCENARIO->value,
            ])
            ->assertRedirect(route('admin.presentaciones.index'))
            ->assertSessionHas('status');

        $presentacion->refresh();
        $this->assertSame(PresentacionEstadoEnum::EN_ESCENARIO, $presentacion->estado);
        $this->assertNull($presentacion->cronometro_iniciado_at);
        $this->assertNull($presentacion->cronometro_finalizado_at);
    }

    #[Test]
    public function al_iniciar_la_presentacion_arranca_el_cronometro(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($admin)->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::EN_ESCENARIO->value]);

        $this->actingAs($admin)
            ->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::PARTICIPANDO->value])
            ->assertRedirect();

        $presentacion->refresh();
        $this->assertSame(PresentacionEstadoEnum::PARTICIPANDO, $presentacion->estado);
        $this->assertNotNull($presentacion->cronometro_iniciado_at);
        $this->assertTrue($presentacion->cronometroCorriendo());
    }

    #[Test]
    public function al_finalizar_se_detiene_el_cronometro(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($admin)->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::EN_ESCENARIO->value]);
        $this->actingAs($admin)->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::PARTICIPANDO->value]);

        $this->actingAs($admin)
            ->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::FINALIZADO->value])
            ->assertRedirect();

        $presentacion->refresh();
        $this->assertSame(PresentacionEstadoEnum::FINALIZADO, $presentacion->estado);
        $this->assertNotNull($presentacion->cronometro_finalizado_at);
        $this->assertFalse($presentacion->cronometroCorriendo());
    }

    #[Test]
    public function no_permite_transiciones_invalidas(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($admin)
            ->from(route('admin.presentaciones.index'))
            ->post(route('admin.presentaciones.transicion', $presentacion), [
                'estado' => PresentacionEstadoEnum::FINALIZADO->value,
            ])
            ->assertRedirect(route('admin.presentaciones.index'))
            ->assertSessionHas('error');

        $presentacion->refresh();
        $this->assertSame(PresentacionEstadoEnum::POR_PARTICIPAR, $presentacion->estado);
    }

    #[Test]
    public function un_jurado_no_puede_transicionar_presentaciones(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($jurado)
            ->post(route('admin.presentaciones.transicion', $presentacion), ['estado' => PresentacionEstadoEnum::EN_ESCENARIO->value])
            ->assertForbidden();

        $this->assertSame(PresentacionEstadoEnum::POR_PARTICIPAR, $presentacion->fresh()->estado);
    }
}