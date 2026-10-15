<?php

namespace Tests\Unit\Calificaciones;

use App\Modules\Calificaciones\Events\CalificacionCorregida;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\CalificacionHistorial;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Calificaciones\Services\CorregirCalificacionService;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class CorregirCalificacionServiceTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected CorregirCalificacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->service = app(CorregirCalificacionService::class);
    }

    public function test_corrige_calificacion_en_presentacion_finalizada_y_crea_historial(): void
    {
        Event::fake([CalificacionCorregida::class]);

        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::FINALIZADO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros()->orderBy('orden')->get();

        // Crear calificación previa con 80 pts
        $calificacion = Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 80.0,
            'estado' => CalificacionEstadoEnum::EMITIDO,
        ]);

        foreach ($params as $p) {
            DetalleCalificacion::create([
                'id_calificacion' => $calificacion->id,
                'id_parametro' => $p->id_parametro,
                'valor' => $p->valor_maximo * 0.8,
            ]);
        }

        // Nuevos valores para dar 100 pts
        $nuevosValores = [];
        foreach ($params as $p) {
            $nuevosValores[$p->id_parametro] = $p->valor_maximo;
        }

        $calificacionCorregida = $this->service->corregir($calificacion, $nuevosValores, $jurado, 'Ajuste de precisión acordado');

        $this->assertSame(100.0, (float) $calificacionCorregida->nota_total);
        $this->assertSame(CalificacionEstadoEnum::CORREGIDO, $calificacionCorregida->estado);

        // Verificar historial
        $historial = CalificacionHistorial::where('id_calificacion', $calificacion->id)->first();
        $this->assertNotNull($historial);
        $this->assertSame($jurado->id, $historial->id_usuario);
        $this->assertSame('Ajuste de precisión acordado', $historial->motivo);
        $this->assertSame(80.0, (float) $historial->datos_anteriores['nota_total']);
        $this->assertSame(100.0, (float) $historial->datos_nuevos['nota_total']);

        // Verificar nota final de presentación
        $presentacion->refresh();
        $this->assertSame(100.0, (float) $presentacion->nota_final);

        Event::assertDispatched(CalificacionCorregida::class);
    }

    public function test_bloquea_correccion_si_la_presentacion_no_ha_finalizado(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO; // Aún no finalizado
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $calificacion = Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 80.0,
            'estado' => CalificacionEstadoEnum::EMITIDO,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;
        $nuevosValores = [];
        foreach ($params as $p) {
            $nuevosValores[$p->id_parametro] = $p->valor_maximo;
        }

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Solo es posible corregir una calificación cuando la presentación ha finalizado');

        $this->service->corregir($calificacion, $nuevosValores, $jurado);
    }
}
