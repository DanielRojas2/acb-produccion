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

class CorreccionMotivoObligatorioTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_correccion_falla_si_no_se_envia_motivo(): void
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

        // Intento de corregir sin motivo
        $response = $this->actingAs($jurado)->post(route('jurado.calificaciones.corregir', $calificacion), [
            'valores' => $nuevosValores,
            // 'motivo' omitido
        ]);

        $response->assertSessionHasErrors(['motivo']);

        // Intento con motivo vacío o con menos de 3 caracteres
        $responseVacio = $this->actingAs($jurado)->post(route('jurado.calificaciones.corregir', $calificacion), [
            'valores' => $nuevosValores,
            'motivo' => 'ab',
        ]);

        $responseVacio->assertSessionHasErrors(['motivo']);

        // Verificar que no se alteró la calificación
        $calificacion->refresh();
        $this->assertSame(80.0, (float) $calificacion->nota_total);
        $this->assertSame(CalificacionEstadoEnum::EMITIDO, $calificacion->estado);
        $this->assertDatabaseMissing('calificacion_historial', [
            'id_calificacion' => $calificacion->id,
        ]);
    }

    public function test_correccion_exitosa_con_motivo_obligatorio_valido(): void
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
            'motivo' => 'Rectificación obligatoria tras revisión técnica con la mesa de jurados',
        ]);

        $response->assertRedirect(route('jurado.inicio'));
        $response->assertSessionHas('success');

        $calificacion->refresh();
        $this->assertSame(100.0, (float) $calificacion->nota_total);
        $this->assertSame(CalificacionEstadoEnum::CORREGIDO, $calificacion->estado);

        $this->assertDatabaseHas('calificacion_historial', [
            'id_calificacion' => $calificacion->id,
            'id_usuario' => $jurado->id,
            'motivo' => 'Rectificación obligatoria tras revisión técnica con la mesa de jurados',
        ]);
    }
}
