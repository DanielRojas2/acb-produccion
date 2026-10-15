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

class EscenarioVistaTest extends TestCase
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
    public function un_admin_puede_ver_el_escenario(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::EN_ESCENARIO])->save();

        $this->actingAs($admin)
            ->get(route('admin.presentaciones.escenario'))
            ->assertOk()
            ->assertSee('Presentación de prueba')
            ->assertSee('En escenario');
    }

    #[Test]
    public function un_jurado_puede_ver_la_presentacion_actual(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();

        $this->actingAs($jurado)
            ->get(route('jurado.presentaciones.actual'))
            ->assertOk()
            ->assertSee('Presentación de prueba')
            ->assertSee('Participando');
    }

    #[Test]
    public function el_escenario_muestra_la_siguiente_presentacion_cuando_no_hay_una_en_curso(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $this->presentacionPreseleccion();

        $this->actingAs($admin)
            ->get(route('admin.presentaciones.escenario'))
            ->assertOk()
            ->assertSee('Siguiente');
    }

    #[Test]
    public function el_escenario_requiere_autenticacion(): void
    {
        $this->get(route('admin.presentaciones.escenario'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function un_visitante_no_autenticado_no_ve_el_escenario_del_jurado(): void
    {
        $this->get(route('jurado.presentaciones.actual'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function jurado_asignado_ve_formulario_de_calificacion_en_columna_derecha(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();

        \App\Modules\Jurados\Models\JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $this->actingAs($jurado)
            ->get(route('jurado.presentaciones.actual'))
            ->assertOk()
            ->assertSee('Calificar Participante')
            ->assertSee('Registrar calificación oficial');
    }

    #[Test]
    public function jurado_con_voto_emitido_ve_resumen_y_desglose_en_escenario(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::FINALIZADO])->save();

        $jp = \App\Modules\Jurados\Models\JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        \App\Modules\Calificaciones\Models\Calificacion::create([
            'id_jurado_presentacion' => $jp->id,
            'nota_total' => 92.50,
            'estado' => \App\Support\Enums\CalificacionEstadoEnum::EMITIDO,
        ]);

        $this->actingAs($jurado)
            ->get(route('jurado.presentaciones.actual'))
            ->assertOk()
            ->assertSee('Calificación Registrada')
            ->assertSee('92.50')
            ->assertSee('Corregir calificación');
    }

    #[Test]
    public function presentacion_finalizada_muestra_aviso_para_registrar_calificaciones(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $presentacion->forceFill([
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'cronometro_finalizado_at' => now(),
        ])->save();

        $this->actingAs($jurado)
            ->get(route('jurado.presentaciones.actual'))
            ->assertOk()
            ->assertSee('Presentación Finalizada');
    }

    #[Test]
    public function muestra_imagen_del_participante_si_esta_cargada(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $participante = \App\Modules\Participantes\Models\Participante::create([
            'nombre' => 'Juan',
            'paterno' => 'Perez',
            'foto' => 'participantes/foto_test.jpg',
        ]);
        $presentacion->inscripcion->participantes()->attach($participante->id);
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();

        $this->actingAs($jurado)
            ->get(route('jurado.presentaciones.actual'))
            ->assertOk()
            ->assertSee('participantes/foto_test.jpg');
    }

    #[Test]
    public function muestra_imagen_sin_fondo_para_jurado_en_calificar(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);
        $presentacion = $this->presentacionPreseleccion();
        $participante = \App\Modules\Participantes\Models\Participante::create([
            'nombre' => 'María',
            'paterno' => 'Flores',
            'foto' => 'participantes/con_fondo.jpg',
            'foto_sin_fondo' => 'participantes/sin_fondo_jurado.png',
        ]);
        $presentacion->inscripcion->participantes()->attach($participante->id);
        $presentacion->forceFill(['estado' => PresentacionEstadoEnum::PARTICIPANDO])->save();

        \App\Modules\Jurados\Models\JuradoPresentacion::create([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ]);

        $this->actingAs($jurado)
            ->get(route('jurado.calificaciones.evaluar', $presentacion))
            ->assertOk()
            ->assertSee('participantes/sin_fondo_jurado.png');
    }
}