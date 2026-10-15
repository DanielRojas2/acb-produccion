<?php

namespace Tests\Feature\Calificaciones;

use App\Modules\Calificaciones\Models\Calificacion;
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

class JuradoCalificacionTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_jurado_asignado_puede_ver_pantalla_de_calificacion_en_presentacion_en_curso(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $response = $this->actingAs($jurado)->get(route('jurado.calificaciones.evaluar', $presentacion));

        $response->assertOk();
        $response->assertSee('Evaluación de Participante');
        $response->assertSee($presentacion->nombreMostrado());
    }

    public function test_jurado_no_asignado_recibe_403(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::PARTICIPANDO;
        $presentacion->save();

        $juradoNoAsignado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $response = $this->actingAs($juradoNoAsignado)->get(route('jurado.calificaciones.evaluar', $presentacion));

        $response->assertForbidden();
    }

    public function test_jurado_emite_calificacion_via_http(): void
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

        $response = $this->actingAs($jurado)->post(route('jurado.calificaciones.guardar', $presentacion), [
            'valores' => $valores,
        ]);

        $response->assertRedirect(route('jurado.inicio'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calificaciones', [
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 100.0,
            'estado' => CalificacionEstadoEnum::EMITIDO->value,
        ]);
    }

    public function test_jurado_puede_corregir_su_calificacion_en_presentacion_finalizada(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->estado = PresentacionEstadoEnum::FINALIZADO;
        $presentacion->save();

        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $jp = JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $categoria = $presentacion->inscripcion->categoria;
        $params = $categoria->categoriaParametros;

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

        $nuevosValores = [];
        foreach ($params as $p) {
            $nuevosValores[$p->id_parametro] = $p->valor_maximo;
        }

        $response = $this->actingAs($jurado)->post(route('jurado.calificaciones.corregir', $calificacion), [
            'valores' => $nuevosValores,
            'motivo' => 'Corrección aprobada',
        ]);

        $response->assertRedirect(route('jurado.inicio'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calificaciones', [
            'id' => $calificacion->id,
            'nota_total' => 100.0,
            'estado' => CalificacionEstadoEnum::CORREGIDO->value,
        ]);

        $this->assertDatabaseHas('calificacion_historial', [
            'id_calificacion' => $calificacion->id,
            'id_usuario' => $jurado->id,
            'motivo' => 'Corrección aprobada',
        ]);
    }
}
