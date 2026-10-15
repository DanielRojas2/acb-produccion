<?php

namespace Tests\Unit\Presentaciones;

use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Presentaciones\Services\GestionarCronometroService;
use App\Support\Enums\PresentacionEstadoEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class GestionarCronometroServiceTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    private GestionarCronometroService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartamentoSeeder::class,
            FaseSeeder::class,
            CategoriaParametrosSeeder::class,
        ]);

        $this->servicio = new GestionarCronometroService();
    }

    #[Test]
    public function iniciar_registra_el_momento_de_inicio(): void
    {
        Carbon::setTestNow('2026-11-20 19:00:00');

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->servicio->iniciar($presentacion);

        $presentacion->refresh();

        $this->assertNotNull($presentacion->cronometro_iniciado_at);
        $this->assertSame('2026-11-20 19:00:00', $presentacion->cronometro_iniciado_at->toDateTimeString());
        $this->assertNull($presentacion->cronometro_finalizado_at);
        $this->assertTrue($presentacion->cronometroCorriendo());
    }

    #[Test]
    public function no_inicia_un_cronometro_ya_en_curso(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();

        $this->servicio->iniciar($presentacion);

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->iniciar($presentacion->refresh());
    }

    #[Test]
    public function no_inicia_si_la_presentacion_aun_no_esta_en_escenario(): void
    {
        $presentacion = $this->presentacionPreseleccion();

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->iniciar($presentacion);
    }

    #[Test]
    public function detener_registra_el_momento_de_finalizacion(): void
    {
        Carbon::setTestNow('2026-11-20 19:00:00');

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();
        $this->servicio->iniciar($presentacion);

        Carbon::setTestNow('2026-11-20 19:02:00');
        $this->servicio->detener($presentacion);

        $presentacion->refresh();

        $this->assertNotNull($presentacion->cronometro_finalizado_at);
        $this->assertSame('2026-11-20 19:02:00', $presentacion->cronometro_finalizado_at->toDateTimeString());
        $this->assertFalse($presentacion->cronometroCorriendo());
    }

    #[Test]
    public function no_detiene_un_cronometro_que_no_esta_corriendo(): void
    {
        $presentacion = $this->presentacionPreseleccion();

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->detener($presentacion);
    }

    #[Test]
    public function obtener_estado_refleja_el_tiempo_transcurrido_y_restante(): void
    {
        Carbon::setTestNow('2026-11-20 19:00:00');

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();
        $this->servicio->iniciar($presentacion);

        $estado = $this->servicio->obtenerEstado($presentacion);
        $this->assertTrue($estado['corriendo']);
        $this->assertSame(0, $estado['segundos_transcurridos']);
        $this->assertSame(300, $estado['segundos_restantes']);

        Carbon::setTestNow('2026-11-20 19:03:00');
        $estado = $this->servicio->obtenerEstado($presentacion);
        $this->assertSame(180, $estado['segundos_transcurridos']);
        $this->assertSame(120, $estado['segundos_restantes']);
    }

    #[Test]
    public function reiniciar_deja_el_cronometro_en_cero(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();
        $this->servicio->iniciar($presentacion);

        $this->servicio->reiniciar($presentacion->refresh());

        $presentacion->refresh();
        $this->assertNull($presentacion->cronometro_iniciado_at);
        $this->assertNull($presentacion->cronometro_finalizado_at);
        $this->assertFalse($presentacion->cronometroCorriendo());
    }
}