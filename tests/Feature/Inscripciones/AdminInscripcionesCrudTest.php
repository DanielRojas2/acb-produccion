<?php

namespace Tests\Feature\Inscripciones;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Inscripciones\Models\InscripcionParticipante;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminInscripcionesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartamentoSeeder::class,
            FaseSeeder::class,
            CategoriaParametrosSeeder::class,
        ]);
    }

    private function admin(): Usuario
    {
        return Usuario::factory()->administrador()->create();
    }

    private function datosInscripcion(array $overrides = []): array
    {
        return array_replace_recursive([
            'categoria' => 'Solista vocal (con o sin acompañamiento)',
            'departamento' => 'Oruro',
            'fase' => 'PRESELECCION',
            'fecha_inscripcion' => '2026-09-11',
            'fecha_presentacion' => '2026-10-05',
            'hora_presentacion' => '19:00',
            'nombre_presentacion' => null,
            'integrantes' => [
                ['nombre' => 'Camila', 'paterno' => 'Mamani', 'materno' => 'Rivera', 'pseudonimo' => null],
            ],
        ], $overrides);
    }

    private function countEstado(): array
    {
        return [
            Inscripcion::count(),
            InscripcionParticipante::count(),
            Presentacion::count(),
            Participante::count(),
        ];
    }

    #[Test]
    public function un_jurado_no_puede_gestionar_inscripciones(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $this->actingAs($jurado)
            ->get(route('admin.inscripciones.index'))
            ->assertForbidden();
    }

    #[Test]
    public function un_admin_puede_registrar_una_inscripcion_completa(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion())
            ->assertRedirect(route('admin.inscripciones.index'));

        $this->assertSame([1, 1, 1, 1], $this->countEstado());

        $inscripcion = Inscripcion::first();
        $categoria = Categoria::where('categoria', 'Solista vocal (con o sin acompañamiento)')->first();

        $this->assertSame($categoria->id, $inscripcion->id_categoria);
        $this->assertSame(1, $inscripcion->participantes()->count());
        $this->assertNotNull($inscripcion->departamento);

        $presentacion = $inscripcion->presentacion;
        $this->assertSame(PresentacionEstadoEnum::POR_PARTICIPAR, $presentacion->estado);
        $this->assertSame($categoria->tiempo_presentacion, $presentacion->tiempo_maximo);
        $this->assertSame('Camila Mamani Rivera', $presentacion->nombre_presentacion);
    }

    #[Test]
    public function registra_un_duo_con_nombre_de_presentacion(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion([
                'categoria' => 'Dúo vocal instrumental',
                'nombre_presentacion' => 'Dúo Andino',
                'integrantes' => [
                    ['nombre' => 'Juan', 'paterno' => 'Pérez', 'materno' => null, 'pseudonimo' => null],
                    ['nombre' => 'María', 'paterno' => 'López', 'materno' => null, 'pseudonimo' => null],
                ],
            ]))
            ->assertRedirect(route('admin.inscripciones.index'));

        $this->assertSame('Dúo Andino', Inscripcion::first()->presentacion->nombre_presentacion);
        $this->assertSame(2, Inscripcion::first()->participantes()->count());
    }

    #[Test]
    public function reutiliza_participantes_ya_existentes(): void
    {
        $participante = Participante::create(['nombre' => 'Camila', 'paterno' => 'Mamani', 'materno' => 'Rivera']);

        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion())
            ->assertRedirect(route('admin.inscripciones.index'));

        $this->assertSame(1, Participante::count());
        $this->assertSame($participante->id, Participante::first()->id);
    }

    #[Test]
    public function una_inscripcion_final_no_exige_departamento(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion([
                'fase' => 'FINAL',
                'departamento' => null,
                'fecha_presentacion' => null,
                'hora_presentacion' => null,
            ]))
            ->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion = Inscripcion::first();
        $this->assertNull($inscripcion->id_departamento);
        $this->assertNull($inscripcion->fase?->departamento_id);
    }

    #[Test]
    public function una_inscripcion_final_puede_registrar_departamento_representado(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion([
                'fase' => 'FINAL',
                'departamento' => 'Oruro',
            ]))
            ->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion = Inscripcion::first();
        $this->assertNotNull($inscripcion->id_departamento);
        $this->assertSame('Oruro', $inscripcion->departamento?->nombre);
        $this->assertSame('FINAL', $inscripcion->fase?->fase?->value);
    }

    #[Test]
    public function sin_departamento_en_preseleccion_no_se_guarda_nada(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion(['departamento' => null]))
            ->assertSessionHasErrors('departamento');

        $this->assertSame([0, 0, 0, 0], $this->countEstado());
    }

    #[Test]
    public function sin_integrantes_no_se_guarda_nada(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion(['integrantes' => [
                ['nombre' => '', 'paterno' => '', 'materno' => null, 'pseudonimo' => null],
            ]]))
            ->assertSessionHasErrors('integrantes');

        $this->assertSame([0, 0, 0, 0], $this->countEstado());
    }

    #[Test]
    public function exceder_el_maximo_de_integrantes_no_se_guarda_nada(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion([
                'categoria' => 'Ñusta',
                'integrantes' => [
                    ['nombre' => 'Ana', 'paterno' => null, 'materno' => null, 'pseudonimo' => null],
                    ['nombre' => 'Luis', 'paterno' => null, 'materno' => null, 'pseudonimo' => null],
                ],
            ]))
            ->assertSessionHasErrors('integrantes');

        $this->assertSame([0, 0, 0, 0], $this->countEstado());
    }

    #[Test]
    public function categoria_inexistente_no_se_guarda_nada(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.inscripciones.store'), $this->datosInscripcion(['categoria' => 'Inexistente']))
            ->assertSessionHasErrors('categoria');

        $this->assertSame([0, 0, 0, 0], $this->countEstado());
    }

    #[Test]
    public function un_admin_puede_editar_y_sincronizar_integrantes(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $this->datosInscripcion([
            'categoria' => 'Dúo vocal instrumental',
            'fecha_presentacion' => '2026-10-05',
            'integrantes' => [
                ['nombre' => 'Juan', 'paterno' => 'Pérez', 'materno' => null, 'pseudonimo' => null],
                ['nombre' => 'María', 'paterno' => 'López', 'materno' => null, 'pseudonimo' => null],
            ],
        ]));

        $inscripcion = Inscripcion::first();

        $this->actingAs($admin)
            ->get(route('admin.inscripciones.edit', $inscripcion))
            ->assertOk()
            ->assertSee('Juan');

        $this->actingAs($admin)
            ->patch(route('admin.inscripciones.update', $inscripcion), $this->datosInscripcion([
                'categoria' => 'Dúo vocal instrumental',
                'fecha_presentacion' => '2026-10-06',
                'integrantes' => [
                    ['nombre' => 'Juan', 'paterno' => 'Pérez', 'materno' => null, 'pseudonimo' => null],
                ],
            ]))
            ->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion->refresh();
        $this->assertSame(1, $inscripcion->participantes()->count());
        $this->assertSame('2026-10-06', $inscripcion->fecha_presentacion->toDateString());
        $this->assertSame('Juan Pérez', $inscripcion->presentacion->nombre_presentacion);
    }

    #[Test]
    public function cambiar_categoria_recalcula_el_tiempo_de_presentacion(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $this->datosInscripcion([
            'categoria' => 'Ñusta',
            'nombre_presentacion' => 'Flor de Oruro',
        ]));

        $inscripcion = Inscripcion::first();
        $this->assertSame(10, $inscripcion->presentacion->tiempo_maximo);

        $this->actingAs($admin)->patch(route('admin.inscripciones.update', $inscripcion), $this->datosInscripcion([
            'categoria' => 'Autóctono',
            'nombre_presentacion' => 'Flor de Oruro',
        ]));

        $inscripcion->refresh();
        $this->assertSame(7, $inscripcion->presentacion->tiempo_maximo);
        $this->assertSame('Flor de Oruro', $inscripcion->presentacion->nombre_presentacion);
    }

    #[Test]
    public function bloquea_editar_y_eliminar_si_la_presentacion_ya_esta_en_uso(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $this->datosInscripcion());

        $inscripcion = Inscripcion::first();
        $inscripcion->presentacion->update(['estado' => PresentacionEstadoEnum::EN_ESCENARIO->value]);

        $this->actingAs($admin)
            ->get(route('admin.inscripciones.edit', $inscripcion))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.inscripciones.update', $inscripcion), $this->datosInscripcion())
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.inscripciones.destroy', $inscripcion))
            ->assertForbidden();

        $this->assertDatabaseHas('inscripciones', ['id' => $inscripcion->id]);
    }

    #[Test]
    public function un_admin_puede_eliminar_una_inscripcion_por_participar(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $this->datosInscripcion());

        $inscripcion = Inscripcion::first();

        $this->actingAs($admin)
            ->delete(route('admin.inscripciones.destroy', $inscripcion))
            ->assertRedirect(route('admin.inscripciones.index'));

        $this->assertSame(0, Inscripcion::count());
        $this->assertSame(0, InscripcionParticipante::count());
        $this->assertSame(0, Presentacion::count());
        $this->assertSame(1, Participante::count());
    }

    #[Test]
    public function un_admin_puede_cargar_foto_con_fondo_y_sin_fondo_al_crear_inscripcion(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = $this->admin();
        $fotoConFondo = \Illuminate\Http\UploadedFile::fake()->image('fondo.jpg', 600, 600);
        $fotoSinFondo = \Illuminate\Http\UploadedFile::fake()->image('jurado.png', 600, 600);

        $datos = $this->datosInscripcion();
        $datos['foto'] = $fotoConFondo;
        $datos['foto_sin_fondo'] = $fotoSinFondo;

        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $datos)
            ->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion = Inscripcion::firstOrFail();
        $this->assertNotNull($inscripcion->presentacion->foto);
        $this->assertNotNull($inscripcion->presentacion->foto_sin_fondo);
        $this->assertSame($inscripcion->presentacion->fotoSinFondoUrl(), $inscripcion->presentacion->fotoJuradoUrl());

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($inscripcion->presentacion->foto);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($inscripcion->presentacion->foto_sin_fondo);
    }

    #[Test]
    public function un_admin_puede_actualizar_y_eliminar_foto_sin_fondo_en_inscripcion(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.inscripciones.store'), $this->datosInscripcion());
        $inscripcion = Inscripcion::firstOrFail();

        $fotoSinFondo = \Illuminate\Http\UploadedFile::fake()->image('jurado.png', 600, 600);
        $this->actingAs($admin)->patch(route('admin.inscripciones.update', $inscripcion), [
            ...$this->datosInscripcion(),
            'foto_sin_fondo' => $fotoSinFondo,
        ])->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion->refresh();
        $this->assertNotNull($inscripcion->presentacion->foto_sin_fondo);

        // Ahora eliminar foto sin fondo
        $this->actingAs($admin)->patch(route('admin.inscripciones.update', $inscripcion), [
            ...$this->datosInscripcion(),
            'eliminar_foto_sin_fondo' => '1',
        ])->assertRedirect(route('admin.inscripciones.index'));

        $inscripcion->refresh();
        $this->assertNull($inscripcion->presentacion->foto_sin_fondo);
    }
}
