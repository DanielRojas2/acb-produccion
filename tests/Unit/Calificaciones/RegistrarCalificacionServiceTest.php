<?php

namespace Tests\Unit\Calificaciones;

use App\Modules\Calificaciones\Events\CalificacionRegistrada;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Services\RegistrarCalificacionService;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class RegistrarCalificacionServiceTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected RegistrarCalificacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->service = app(RegistrarCalificacionService::class);
    }

    public function test_registra_calificacion_y_detalles_correctamente(): void
    {
        Event::fake([CalificacionRegistrada::class]);

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;

        $valores = [];
        foreach ($params as $p) {
            $valores[$p->id_parametro] = $p->valor_maximo; // Puntaje máximo para cada parámetro
        }

        $calificacion = $this->service->registrar($jp, $valores, $jurado);

        $this->assertInstanceOf(Calificacion::class, $calificacion);
        $this->assertSame(100.0, (float) $calificacion->nota_total);
        $this->assertSame(CalificacionEstadoEnum::EMITIDO, $calificacion->estado);
        $this->assertCount($params->count(), $calificacion->detalles);

        $jp->refresh();
        $this->assertSame(JuradoPresentacionEstadoEnum::FINALIZADO, $jp->estado);

        $presentacion->refresh();
        $this->assertSame(100.0, (float) $presentacion->nota_final);

        Event::assertDispatched(CalificacionRegistrada::class);
    }

    public function test_bloquea_calificacion_si_la_presentacion_esta_por_participar(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::POR_PARTICIPAR;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;
        $valores = [$params[0]->id_parametro => 10];

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se puede registrar una calificación cuando la presentación está en estado');

        $this->service->registrar($jp, $valores, $jurado);
    }

    public function test_bloquea_si_un_jurado_no_asignado_intenta_calificar(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO;
        $presentacion->save();

        $juradoAsignado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $otroJurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $juradoAsignado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;
        $valores = [$params[0]->id_parametro => 10];

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No tiene autorización para registrar la calificación');

        $this->service->registrar($jp, $valores, $otroJurado);
    }

    public function test_bloquea_doble_emision_de_voto_inicial(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;
        $valores = [];
        foreach ($params as $p) {
            $valores[$p->id_parametro] = $p->valor_maximo;
        }

        // Primer voto
        $this->service->registrar($jp, $valores, $jurado);

        // Intento de segundo voto directo
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Este jurado ya emitió su calificación');

        $this->service->registrar($jp, $valores, $jurado);
    }
}
