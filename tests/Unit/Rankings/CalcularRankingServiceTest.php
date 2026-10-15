<?php

namespace Tests\Unit\Rankings;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Categorias\Models\CategoriaParametro;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Jurados\Services\AsignarJuradosService;
use App\Modules\Jurados\Services\GestionarJuradoSecretoService;
use App\Modules\Parametros\Models\Parametro;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Rankings\Services\CalcularRankingService;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalcularRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalcularRankingService $rankingService;
    private AsignarJuradosService $asignarService;
    private GestionarJuradoSecretoService $secretoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rankingService = app(CalcularRankingService::class);
        $this->asignarService = app(AsignarJuradosService::class);
        $this->secretoService = app(GestionarJuradoSecretoService::class);
    }

    private function setupEscenario(): array
    {
        $categoria = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
        ]);

        $parametro = Parametro::create(['parametro' => 'Interpretación vocal']);
        CategoriaParametro::create([
            'id_categoria' => $categoria->id,
            'id_parametro' => $parametro->id,
            'valor_minimo' => 1,
            'valor_maximo' => 100,
            'orden' => 1,
        ]);

        $fase = Fase::create(['fase' => FaseEnum::FINAL->value]);
        $departamento = Departamento::create(['nombre' => 'Cochabamba']);

        $participante1 = Participante::create(['nombre' => 'Lucia', 'paterno' => 'Mendez']);
        $inscripcion1 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria->id,
            'id_departamento' => $departamento->id,
            'estado' => \App\Support\Enums\InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $inscripcion1->participantes()->attach($participante1->id);
        $presentacion1 = Presentacion::create([
            'id_inscripcion' => $inscripcion1->id,
            'nombre_presentacion' => 'Lucia Mendez',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 95.0,
        ]);

        $participante2 = Participante::create(['nombre' => 'Carla', 'paterno' => 'Rojas']);
        $inscripcion2 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria->id,
            'id_departamento' => $departamento->id,
            'estado' => \App\Support\Enums\InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $inscripcion2->participantes()->attach($participante2->id);
        $presentacion2 = Presentacion::create([
            'id_inscripcion' => $inscripcion2->id,
            'nombre_presentacion' => 'Carla Rojas',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 88.0,
        ]);

        $jurados = [];
        for ($i = 1; $i <= 5; $i++) {
            $jurados[] = Usuario::create([
                'username' => "jurado{$i}_".uniqid(),
                'nombre' => "Jurado{$i}",
                'paterno' => "Paterno{$i}",
                'ci' => "999{$i}".rand(10, 99),
                'email' => "j{$i}_".uniqid().'@test.com',
                'password' => 'password',
                'rol' => RolEnum::JURADO,
            ]);
        }

        $this->asignarService->asignar($presentacion1, array_map(fn ($j) => $j->id, $jurados), $jurados[4]->id);
        $this->asignarService->asignar($presentacion2, array_map(fn ($j) => $j->id, $jurados), $jurados[4]->id);

        // Crear calificaciones para presentacion1
        foreach ($presentacion1->jurados as $jp) {
            $cal = Calificacion::create([
                'id_jurado_presentacion' => $jp->id,
                'nota_total' => 95.0,
                'estado' => CalificacionEstadoEnum::EMITIDO,
                'fecha_finalizacion' => now(),
            ]);
            DetalleCalificacion::create([
                'id_calificacion' => $cal->id,
                'id_parametro' => $parametro->id,
                'valor' => 95.0,
            ]);
        }

        return compact('categoria', 'presentacion1', 'presentacion2', 'jurados', 'fase');
    }

    public function test_ranking_ordena_por_nota_final_descendente(): void
    {
        $data = $this->setupEscenario();
        $categoria = $data['categoria'];

        $ranking = $this->rankingService->rankingPorCategoria($categoria, null, true);

        $this->assertCount(2, $ranking);
        $this->assertEquals('Lucia Mendez', $ranking[0]['nombre']);
        $this->assertEquals(1, $ranking[0]['posicion']);
        $this->assertEquals(95.0, $ranking[0]['nota_final']);

        $this->assertEquals('Carla Rojas', $ranking[1]['nombre']);
        $this->assertEquals(2, $ranking[1]['posicion']);
        $this->assertEquals(88.0, $ranking[1]['nota_final']);
    }

    public function test_ranking_enmascara_jurado_secreto_para_publico_si_no_esta_revelada(): void
    {
        $data = $this->setupEscenario();
        $categoria = $data['categoria'];

        $this->assertFalse($categoria->estaRevelada());

        $ranking = $this->rankingService->rankingPorCategoria($categoria, null, true);
        $juradosPresentacion1 = $ranking[0]['jurados'];

        $juradoSecreto = $juradosPresentacion1->firstWhere('es_secreto', true);

        $this->assertNotNull($juradoSecreto);
        $this->assertEquals('Jurado Secreto', $juradoSecreto['jurado_nombre']);
        $this->assertNull($juradoSecreto['jurado_id']);
        $this->assertNull($juradoSecreto['nota_total']);
        $this->assertFalse($juradoSecreto['es_revelado']);
    }

    public function test_ranking_muestra_jurado_secreto_para_publico_cuando_esta_revelada(): void
    {
        $data = $this->setupEscenario();
        $categoria = $data['categoria'];
        $juradoSecretoOriginal = $data['jurados'][4];

        $this->secretoService->revelarJuradoSecreto($categoria);
        $this->assertTrue($categoria->fresh()->estaRevelada());

        $ranking = $this->rankingService->rankingPorCategoria($categoria->fresh(), null, true);
        $juradosPresentacion1 = $ranking[0]['jurados'];

        $juradoSecreto = $juradosPresentacion1->firstWhere('es_secreto', true);

        $this->assertNotNull($juradoSecreto);
        $this->assertEquals("{$juradoSecretoOriginal->nombre} {$juradoSecretoOriginal->paterno}", $juradoSecreto['jurado_nombre']);
        $this->assertEquals($juradoSecretoOriginal->id, $juradoSecreto['jurado_id']);
        $this->assertEquals(95.0, $juradoSecreto['nota_total']);
        $this->assertTrue($juradoSecreto['es_revelado']);
    }

    public function test_ranking_muestra_todos_los_datos_para_admin(): void
    {
        $data = $this->setupEscenario();
        $categoria = $data['categoria'];
        $juradoSecretoOriginal = $data['jurados'][4];

        $this->assertFalse($categoria->estaRevelada());

        // Para admin ($esPublico = false)
        $rankingAdmin = $this->rankingService->rankingPorCategoria($categoria, null, false);
        $juradosPresentacion1 = $rankingAdmin[0]['jurados'];

        $juradoSecreto = $juradosPresentacion1->firstWhere('es_secreto', true);

        $this->assertNotNull($juradoSecreto);
        $this->assertEquals("{$juradoSecretoOriginal->nombre} {$juradoSecretoOriginal->paterno}", $juradoSecreto['jurado_nombre']);
        $this->assertEquals($juradoSecretoOriginal->id, $juradoSecreto['jurado_id']);
        $this->assertEquals(95.0, $juradoSecreto['nota_total']);
        $this->assertTrue($juradoSecreto['es_revelado']);
    }
}
