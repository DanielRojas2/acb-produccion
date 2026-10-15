<?php

namespace Tests\Feature\Jurados;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Categorias\Models\CategoriaParametro;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Parametros\Models\Parametro;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\InscripcionEstadoEnum;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesaJuradoTest extends TestCase
{
    use RefreshDatabase;

    public function test_jurado_delegado_puede_seleccionar_categoria_activa(): void
    {
        $delegado = Usuario::create([
            'username' => 'jurado_mesa',
            'nombre' => 'Delegado',
            'paterno' => 'Mesa',
            'ci' => '1234567',
            'email' => 'mesa@test.com',
            'password' => 'password',
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => true,
        ]);

        $cat1 = Categoria::create(['categoria' => 'Ñusta', 'max_participantes' => 1, 'tiempo_presentacion' => 10]);
        $cat2 = Categoria::create(['categoria' => 'Especial', 'max_participantes' => 4, 'tiempo_presentacion' => 10]);

        $response = $this->actingAs($delegado)
            ->post(route('mesa.categorias.seleccionar', $cat2));

        $response->assertRedirect();
        $this->assertTrue($cat2->fresh()->es_activa);
        $this->assertFalse($cat1->fresh()->es_activa);
    }

    public function test_jurado_no_delegado_no_puede_seleccionar_categoria(): void
    {
        $juradoComun = Usuario::create([
            'username' => 'jurado_comun',
            'nombre' => 'Vocal',
            'paterno' => 'Simple',
            'ci' => '7654321',
            'email' => 'vocal@test.com',
            'password' => 'password',
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => false,
        ]);

        $cat = Categoria::create(['categoria' => 'Ñusta', 'max_participantes' => 1, 'tiempo_presentacion' => 10]);

        $response = $this->actingAs($juradoComun)
            ->post(route('mesa.categorias.seleccionar', $cat));

        $response->assertStatus(403);
    }

    public function test_jurado_delegado_puede_cerrar_calificaciones_y_revelar_ganador(): void
    {
        $delegado = Usuario::create([
            'username' => 'jurado_controlador',
            'nombre' => 'Controlador',
            'paterno' => 'Oficial',
            'ci' => '5554443',
            'email' => 'ctrl@test.com',
            'password' => 'password',
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => true,
        ]);

        $cat = Categoria::create(['categoria' => 'Composición', 'max_participantes' => 2, 'tiempo_presentacion' => 10]);

        // Cerrar calificaciones
        $responseCerrar = $this->actingAs($delegado)
            ->post(route('mesa.categorias.cerrar', $cat));

        $responseCerrar->assertRedirect();
        $this->assertTrue($cat->fresh()->estaFinalizada());

        // Revelar ganador
        $responseRevelar = $this->actingAs($delegado)
            ->post(route('mesa.categorias.revelar', $cat));

        $responseRevelar->assertRedirect();
        $this->assertTrue($cat->fresh()->estaRevelada());
    }

    public function test_jurado_delegado_ve_panel_de_mesa_con_orden_de_presentacion(): void
    {
        $delegado = Usuario::create([
            'username' => 'jurado_mesa_view',
            'nombre' => 'Delegado',
            'paterno' => 'Mesa',
            'ci' => '9988776',
            'email' => 'view@test.com',
            'password' => 'password',
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => true,
        ]);

        $cat = Categoria::create(['categoria' => 'Autóctono', 'max_participantes' => 6, 'tiempo_presentacion' => 10, 'es_activa' => true]);
        $fase = Fase::create(['fase' => FaseEnum::FINAL->value]);
        $oruro = Departamento::create(['nombre' => 'Oruro']);

        $part = Participante::create(['nombre' => 'Comunidad', 'paterno' => 'Ayllu']);
        $insc = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $cat->id,
            'id_departamento' => $oruro->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc->participantes()->attach($part->id);
        $pres = Presentacion::create([
            'id_inscripcion' => $insc->id,
            'nombre_presentacion' => 'Ayllu Mayor',
            'estado' => PresentacionEstadoEnum::POR_PARTICIPAR,
            'tiempo_maximo' => 10,
        ]);

        $response = $this->actingAs($delegado)
            ->get(route('jurado.inicio'));

        $response->assertStatus(200);
        $response->assertSee('Autóctono');
        $response->assertSee('Ayllu Mayor');
        $response->assertSee('Llamar al Escenario');
        $response->assertSee('Cerrar Calificación');
        $response->assertDontSee('Revelar Ganador');
    }

    public function test_categoria_con_calificaciones_cerradas_bloquea_registro_y_correccion(): void
    {
        $jurado = Usuario::create([
            'username' => 'jurado_eval',
            'nombre' => 'Jurado',
            'paterno' => 'Uno',
            'ci' => '1113334',
            'email' => 'eval@test.com',
            'password' => 'password',
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => false,
        ]);

        $cat = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
            'es_activa' => true,
            'finalizada' => true,
            'finalizada_at' => now(),
        ]);
        $param = Parametro::create(['parametro' => 'Vocal']);
        CategoriaParametro::create([
            'id_categoria' => $cat->id,
            'id_parametro' => $param->id,
            'valor_minimo' => 1,
            'valor_maximo' => 100,
            'orden' => 1,
        ]);

        $fase = Fase::create(['fase' => FaseEnum::FINAL->value]);
        $oruro = Departamento::create(['nombre' => 'Oruro']);

        $part = Participante::create(['nombre' => 'Lucía', 'paterno' => 'Flores']);
        $insc = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $cat->id,
            'id_departamento' => $oruro->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc->participantes()->attach($part->id);
        $pres = Presentacion::create([
            'id_inscripcion' => $insc->id,
            'nombre_presentacion' => 'Lucía Flores',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
        ]);

        JuradoPresentacion::create([
            'id_presentacion' => $pres->id,
            'id_usuario' => $jurado->id,
            'estado' => JuradoPresentacionEstadoEnum::ASIGNADO,
        ]);

        // Intento de evaluar
        $responseEval = $this->actingAs($jurado)
            ->get(route('jurado.calificaciones.evaluar', $pres));

        $responseEval->assertRedirect(route('jurado.inicio'));
        $responseEval->assertSessionHas('error');

        // Intento de guardar
        $responseGuardar = $this->actingAs($jurado)
            ->post(route('jurado.calificaciones.guardar', $pres), [
                'valores' => [$param->id => 90],
            ]);

        $responseGuardar->assertSessionHas('error');
    }
}
