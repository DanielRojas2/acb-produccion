<?php

namespace Tests\Unit\Jurados;

use App\Modules\Calificaciones\Events\CalificacionRegistrada;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Calificaciones\Services\CalcularNotaService;
use App\Modules\Categorias\Events\CategoriaFinalizada;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Categorias\Models\CategoriaParametro;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Jurados\Events\JuradoSecretoRevelado;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Jurados\Services\AsignarJuradosService;
use App\Modules\Jurados\Services\GestionarJuradoSecretoService;
use App\Modules\Parametros\Models\Parametro;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Rankings\Events\RankingActualizado;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use UnexpectedValueException;

class JuradoSecretoServiceTest extends TestCase
{
    use RefreshDatabase;

    private AsignarJuradosService $asignarService;
    private GestionarJuradoSecretoService $secretoService;
    private CalcularNotaService $calcularNotaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asignarService = app(AsignarJuradosService::class);
        $this->secretoService = app(GestionarJuradoSecretoService::class);
        $this->calcularNotaService = app(CalcularNotaService::class);
    }

    private function crearCategoriaConParametros(): Categoria
    {
        $categoria = Categoria::create([
            'categoria' => 'Solista vocal test',
            'max_participantes' => 1,
            'tiempo_presentacion' => 5,
        ]);

        $p1 = Parametro::create(['parametro' => 'Interpretación']);
        $p2 = Parametro::create(['parametro' => 'Técnica vocal']);

        CategoriaParametro::create([
            'id_categoria' => $categoria->id,
            'id_parametro' => $p1->id,
            'valor_minimo' => 1,
            'valor_maximo' => 60,
            'orden' => 1,
        ]);

        CategoriaParametro::create([
            'id_categoria' => $categoria->id,
            'id_parametro' => $p2->id,
            'valor_minimo' => 1,
            'valor_maximo' => 40,
            'orden' => 2,
        ]);

        return $categoria;
    }

    private function crearPresentacion(Categoria $categoria, FaseEnum $faseEnum): Presentacion
    {
        $fase = Fase::create(['fase' => $faseEnum->value]);
        $departamento = Departamento::create(['nombre' => 'Oruro']);

        $participante = Participante::create([
            'nombre' => 'Juan',
            'paterno' => 'Perez',
            'materno' => 'Gomez',
        ]);

        $inscripcion = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria->id,
            'id_departamento' => $departamento->id,
            'estado' => \App\Support\Enums\InscripcionEstadoEnum::CONFIRMADA,
        ]);

        $inscripcion->participantes()->attach($participante->id);

        return Presentacion::create([
            'id_inscripcion' => $inscripcion->id,
            'nombre_presentacion' => 'Juan Perez',
            'estado' => PresentacionEstadoEnum::POR_PARTICIPAR,
            'tiempo_maximo' => 5,
        ]);
    }

    /**
     * @return list<Usuario>
     */
    private function crearJurados(int $cantidad): array
    {
        $jurados = [];
        for ($i = 1; $i <= $cantidad; $i++) {
            $jurados[] = Usuario::create([
                'username' => "jurado{$i}_".uniqid(),
                'nombre' => "Jurado{$i}",
                'paterno' => "Apellido{$i}",
                'ci' => "12345{$i}".rand(100, 999),
                'email' => "jurado{$i}_".uniqid().'@test.com',
                'password' => 'password',
                'rol' => RolEnum::JURADO,
            ]);
        }

        return $jurados;
    }

    public function test_asignar_jurado_secreto_en_etapa_final(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);

        $juradoSecreto = $jurados[2]; // Elegimos el 3er jurado como secreto

        $asignaciones = $this->asignarService->asignar(
            $presentacion,
            array_map(fn ($j) => $j->id, $jurados),
            $juradoSecreto->id
        );

        $this->assertCount(5, $asignaciones);

        $secretos = $presentacion->jurados()->where('es_secreto', true)->get();
        $this->assertCount(1, $secretos);
        $this->assertEquals($juradoSecreto->id, $secretos->first()->id_usuario);
    }

    public function test_bloquea_jurado_secreto_en_preseleccion(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::PRESELECCION);
        $jurados = $this->crearJurados(3);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('La etapa de preselección no permite asignar un jurado secreto.');

        $this->asignarService->asignar(
            $presentacion,
            array_map(fn ($j) => $j->id, $jurados),
            $jurados[0]->id
        );
    }

    public function test_bloquea_jurado_secreto_que_no_pertenece_a_los_asignados(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);
        $otroJurado = $this->crearJurados(1)[0];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('El jurado secreto designado debe pertenecer al grupo de jurados asignados.');

        $this->asignarService->asignar(
            $presentacion,
            array_map(fn ($j) => $j->id, $jurados),
            $otroJurado->id
        );
    }

    public function test_autoasignar_en_etapa_final_asigna_jurado_secreto(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $this->crearJurados(5);

        $creadas = $this->asignarService->autoAsignar($presentacion);

        $this->assertCount(5, $creadas);
        $secretos = $presentacion->jurados()->where('es_secreto', true)->get();
        $this->assertCount(1, $secretos);
    }

    public function test_garantiza_consistencia_de_jurado_secreto_en_toda_la_categoria(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion1 = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $presentacion2 = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);

        // Asignamos a ambas presentaciones
        $this->asignarService->asignar($presentacion1, array_map(fn ($j) => $j->id, $jurados));
        $this->asignarService->asignar($presentacion2, array_map(fn ($j) => $j->id, $jurados));

        // Designamos jurado secreto a nivel de categoría
        $juradoElegido = $jurados[4];
        $this->secretoService->designarJuradoSecretoCategoria($categoria, $juradoElegido->id);

        $secreto1 = $presentacion1->jurados()->where('es_secreto', true)->first();
        $secreto2 = $presentacion2->jurados()->where('es_secreto', true)->first();

        $this->assertNotNull($secreto1);
        $this->assertNotNull($secreto2);
        $this->assertEquals($juradoElegido->id, $secreto1->id_usuario);
        $this->assertEquals($juradoElegido->id, $secreto2->id_usuario);
    }

    public function test_calculo_nota_final_incluye_al_jurado_secreto_internamente(): void
    {
        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);

        $this->asignarService->asignar($presentacion, array_map(fn ($j) => $j->id, $jurados), $jurados[4]->id);

        // Emitimos votos para los 5 jurados: 80, 85, 90, 95, 100
        // Jurado 5 es el jurado secreto con nota 100
        $notas = [80.0, 85.0, 90.0, 95.0, 100.0];
        $p1 = $categoria->parametros->first();
        $p2 = $categoria->parametros->last();

        foreach ($presentacion->jurados as $index => $jp) {
            $notaTotal = $notas[$index];
            $calificacion = Calificacion::create([
                'id_jurado_presentacion' => $jp->id,
                'nota_total' => $notaTotal,
                'estado' => CalificacionEstadoEnum::EMITIDO,
                'fecha_finalizacion' => now(),
            ]);

            DetalleCalificacion::create([
                'id_calificacion' => $calificacion->id,
                'id_parametro' => $p1->id,
                'valor' => $notaTotal * 0.6,
            ]);

            DetalleCalificacion::create([
                'id_calificacion' => $calificacion->id,
                'id_parametro' => $p2->id,
                'valor' => $notaTotal * 0.4,
            ]);
        }

        // Promedio matemático: (80 + 85 + 90 + 95 + 100) / 5 = 90.00
        $notaFinal = $this->calcularNotaService->calcularNotaFinalPresentacion($presentacion);
        $this->assertEquals(90.00, $notaFinal);

        // Actualizamos nota final en modelo
        $presentacion->update(['nota_final' => $notaFinal]);

        // Verificamos que la categoría aún no esté revelada
        $this->assertFalse($categoria->estaRevelada());
        $this->assertEquals(90.00, $presentacion->fresh()->nota_final);

        // Revelamos la categoría y la nota final sigue siendo exactamente 90.00
        $this->secretoService->revelarJuradoSecreto($categoria);
        $this->assertTrue($categoria->fresh()->estaRevelada());
        $this->assertEquals(90.00, $presentacion->fresh()->nota_final);
    }

    public function test_revelar_jurado_secreto_despacha_evento_y_actualiza_estado(): void
    {
        Event::fake([JuradoSecretoRevelado::class, RankingActualizado::class]);

        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);
        $this->asignarService->asignar($presentacion, array_map(fn ($j) => $j->id, $jurados), $jurados[0]->id);

        $this->assertFalse($categoria->estaRevelada());

        $this->secretoService->revelarJuradoSecreto($categoria);

        $this->assertTrue($categoria->fresh()->estaRevelada());
        $this->assertNotNull($categoria->fresh()->revelado_at);

        Event::assertDispatched(JuradoSecretoRevelado::class, function ($event) use ($categoria, $jurados) {
            return $event->categoria->id === $categoria->id && $event->juradoSecreto?->id === $jurados[0]->id;
        });

        Event::assertDispatched(RankingActualizado::class);
    }

    public function test_finalizar_categoria_marca_finalizada_y_revela_jurado_secreto(): void
    {
        Event::fake([CategoriaFinalizada::class, JuradoSecretoRevelado::class, RankingActualizado::class]);

        $categoria = $this->crearCategoriaConParametros();
        $presentacion = $this->crearPresentacion($categoria, FaseEnum::FINAL);
        $jurados = $this->crearJurados(5);
        $this->asignarService->asignar($presentacion, array_map(fn ($j) => $j->id, $jurados), $jurados[1]->id);

        $this->assertFalse($categoria->estaFinalizada());
        $this->assertFalse($categoria->estaRevelada());

        $this->secretoService->finalizarCategoria($categoria);

        $catFresh = $categoria->fresh();
        $this->assertTrue($catFresh->estaFinalizada());
        $this->assertFalse($catFresh->estaRevelada());
        $this->assertNotNull($catFresh->finalizada_at);

        Event::assertDispatched(CategoriaFinalizada::class);
        Event::assertDispatched(RankingActualizado::class);
    }
}
