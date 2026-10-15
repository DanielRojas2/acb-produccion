<?php

namespace Tests\Feature\Rankings;

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
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\InscripcionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicoRankingTest extends TestCase
{
    use RefreshDatabase;

    private function setupEscenarioPublico(): array
    {
        $categoria1 = Categoria::create([
            'categoria' => 'Solista vocal',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
            'es_activa' => true,
        ]);

        $categoria2 = Categoria::create([
            'categoria' => 'Dúo vocal instrumental',
            'max_participantes' => 2,
            'tiempo_presentacion' => 10,
        ]);

        $parametro = Parametro::create(['parametro' => 'Interpretación']);
        CategoriaParametro::create([
            'id_categoria' => $categoria1->id,
            'id_parametro' => $parametro->id,
            'valor_minimo' => 1,
            'valor_maximo' => 100,
            'orden' => 1,
        ]);

        CategoriaParametro::create([
            'id_categoria' => $categoria2->id,
            'id_parametro' => $parametro->id,
            'valor_minimo' => 1,
            'valor_maximo' => 100,
            'orden' => 1,
        ]);

        $fase = Fase::create(['fase' => FaseEnum::FINAL->value]);
        $oruro = Departamento::create(['nombre' => 'Oruro']);
        $laPaz = Departamento::create(['nombre' => 'La Paz']);
        $cbba = Departamento::create(['nombre' => 'Cochabamba']);
        $potosi = Departamento::create(['nombre' => 'Potosí']);

        // Presentación 1 (Oruro, Solista) -> 1° Lugar
        $part1 = Participante::create(['nombre' => 'Mateo', 'paterno' => 'Flores']);
        $insc1 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria1->id,
            'id_departamento' => $oruro->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc1->participantes()->attach($part1->id);
        $pres1 = Presentacion::create([
            'id_inscripcion' => $insc1->id,
            'nombre_presentacion' => 'Mateo Flores',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 96.5,
        ]);

        // Presentación 2 (La Paz, Solista) -> 2° Lugar
        $part2 = Participante::create(['nombre' => 'Valeria', 'paterno' => 'Quiroga']);
        $insc2 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria1->id,
            'id_departamento' => $laPaz->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc2->participantes()->attach($part2->id);
        $pres2 = Presentacion::create([
            'id_inscripcion' => $insc2->id,
            'nombre_presentacion' => 'Valeria Quiroga',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 92.0,
        ]);

        // Presentación 3 (Cochabamba, Solista) -> 3° Lugar
        $part3 = Participante::create(['nombre' => 'Carlos', 'paterno' => 'Mendoza']);
        $insc3 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria1->id,
            'id_departamento' => $cbba->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc3->participantes()->attach($part3->id);
        $pres3 = Presentacion::create([
            'id_inscripcion' => $insc3->id,
            'nombre_presentacion' => 'Carlos Mendoza',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 88.0,
        ]);

        // Presentación 4 (Potosí, Solista) -> 4° Lugar
        $part4 = Participante::create(['nombre' => 'David', 'paterno' => 'Vargas']);
        $insc4 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $categoria1->id,
            'id_departamento' => $potosi->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc4->participantes()->attach($part4->id);
        $pres4 = Presentacion::create([
            'id_inscripcion' => $insc4->id,
            'nombre_presentacion' => 'David Vargas',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'tiempo_maximo' => 10,
            'nota_final' => 84.0,
        ]);

        // Jurados
        $jurados = [];
        for ($i = 1; $i <= 5; $i++) {
            $jurados[] = Usuario::create([
                'username' => "jurado{$i}_".uniqid(),
                'nombre' => "Maestro{$i}",
                'paterno' => "Paterno{$i}",
                'ci' => "888{$i}".rand(10, 99),
                'email' => "m{$i}_".uniqid().'@test.com',
                'password' => 'password',
                'rol' => RolEnum::JURADO,
            ]);
        }

        $asignarService = app(AsignarJuradosService::class);
        $asignarService->asignar($pres1, array_map(fn ($j) => $j->id, $jurados), $jurados[4]->id);

        foreach ($pres1->jurados as $jp) {
            $cal = Calificacion::create([
                'id_jurado_presentacion' => $jp->id,
                'nota_total' => 96.5,
                'estado' => CalificacionEstadoEnum::EMITIDO,
                'fecha_finalizacion' => now(),
            ]);
            DetalleCalificacion::create([
                'id_calificacion' => $cal->id,
                'id_parametro' => $parametro->id,
                'valor' => 96.5,
            ]);
        }

        return compact('categoria1', 'categoria2', 'oruro', 'laPaz', 'pres1', 'pres2', 'pres3', 'pres4', 'jurados');
    }

    public function test_vista_publica_carga_correctamente_sin_boton_de_ingreso(): void
    {
        $response = $this->get(route('inicio'));

        $response->assertStatus(200);
        $response->assertSee('Aquí… Canta Bolivia');
        $response->assertSee('XXXIII Festival Nacional');
        
        // Regla crítica: el botón de ingreso NO debe ser visible
        $response->assertDontSee('Ingresar');
        $response->assertDontSee('/login');
    }

    public function test_vista_publica_muestra_participaciones_de_categoria_activa(): void
    {
        $data = $this->setupEscenarioPublico();

        $response = $this->get(route('inicio'));

        $response->assertStatus(200);
        $response->assertSee('Mateo Flores');
        $response->assertSee('Valeria Quiroga');
        $response->assertSee('Solista vocal');
    }

    public function test_vista_publica_filtra_por_categoria(): void
    {
        $data = $this->setupEscenarioPublico();

        $response = $this->get(route('inicio', ['categoria' => $data['categoria2']->id]));

        $response->assertStatus(200);
        $response->assertSee('Dúo vocal instrumental');
    }

    public function test_vista_publica_enmascara_calificacion_reservada_cuando_no_esta_revelada(): void
    {
        $data = $this->setupEscenarioPublico();
        $categoria1 = $data['categoria1'];

        $this->assertFalse($categoria1->estaRevelada());

        $response = $this->get(route('inicio', ['categoria' => $categoria1->id]));

        $response->assertStatus(200);
        $response->assertSee('🔒 Reservado');
        $juradoSecreto = $data['jurados'][4];
        $response->assertDontSee("{$juradoSecreto->nombre} {$juradoSecreto->paterno}");
    }

    public function test_vista_publica_muestra_podio_f1_al_revelar(): void
    {
        $data = $this->setupEscenarioPublico();
        $categoria1 = $data['categoria1'];
        $juradoSecreto = $data['jurados'][4];

        // Paso 0: Modo misterio (todas tapadas con ?)
        app(GestionarJuradoSecretoService::class)->revelarJuradoSecreto($categoria1, 0);

        $responseMisterio = $this->get(route('inicio', ['categoria' => $categoria1->id]));
        $responseMisterio->assertStatus(200);
        $responseMisterio->assertSee('Ganadores de la Categoría');
        $responseMisterio->assertSee('En Espera');

        // Paso 1: Podio completamente revelado
        app(GestionarJuradoSecretoService::class)->avanzarPasoPodio($categoria1, 1);

        $response = $this->get(route('inicio', ['categoria' => $categoria1->id]));

        $response->assertStatus(200);
        $response->assertSee('Ganadores');
        $response->assertSee('Primer Lugar');
        $response->assertSee('Segundo Lugar');
        $response->assertSee('Tercer Lugar');
        $response->assertSee('Clasificación General');
        $response->assertSee('Mateo Flores');
        $response->assertSee('Valeria Quiroga');
        $response->assertSee('Carlos Mendoza');
        $response->assertSee('David Vargas');
        $response->assertSee('96.50');
    }

    public function test_vista_publica_no_incluye_pantalla_completa_ni_tabla_ni_sidebar(): void
    {
        $data = $this->setupEscenarioPublico();

        $response = $this->get(route('inicio'));

        $response->assertStatus(200);
        $response->assertDontSee('Pantalla Completa');
        $response->assertDontSee('toggleFullscreen');
        $response->assertDontSee('sidebarCategorias');
        $response->assertDontSee('Modo Tabla Oficial');
    }

    public function test_vista_publica_muestra_indicador_de_categoria_activa_con_linea_dorada(): void
    {
        $data = $this->setupEscenarioPublico();

        $response = $this->get(route('inicio'));

        $response->assertStatus(200);
        $response->assertSee('Solista vocal');
        $response->assertSee('#d4af37');
    }

    public function test_vista_publica_muestra_un_solo_ganador_en_categoria_nusta(): void
    {
        $nusta = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 15,
            'es_activa' => true,
        ]);

        $parametro = Parametro::firstOrCreate(['parametro' => 'Interpretación vocal']);
        CategoriaParametro::create([
            'id_categoria' => $nusta->id,
            'id_parametro' => $parametro->id,
            'valor_minimo' => 1,
            'valor_maximo' => 100,
            'orden' => 1,
        ]);

        $fase = Fase::firstOrCreate(['fase' => FaseEnum::FINAL->value]);
        $oruro = Departamento::firstOrCreate(['nombre' => 'Oruro']);
        $cocha = Departamento::firstOrCreate(['nombre' => 'Cochabamba']);

        $part1 = Participante::create(['nombre' => 'Lucía', 'paterno' => 'Méndez']);
        $insc1 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $nusta->id,
            'id_departamento' => $oruro->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc1->participantes()->attach($part1->id);
        Presentacion::create([
            'id_inscripcion' => $insc1->id,
            'nombre_presentacion' => 'Lucía Méndez',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'nota_final' => 95.0,
        ]);

        $part2 = Participante::create(['nombre' => 'Camila', 'paterno' => 'Torres']);
        $insc2 = Inscripcion::create([
            'fecha_inscripcion' => now(),
            'id_fase' => $fase->id,
            'id_categoria' => $nusta->id,
            'id_departamento' => $cocha->id,
            'estado' => InscripcionEstadoEnum::CONFIRMADA,
        ]);
        $insc2->participantes()->attach($part2->id);
        Presentacion::create([
            'id_inscripcion' => $insc2->id,
            'nombre_presentacion' => 'Camila Torres',
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'nota_final' => 88.0,
        ]);

        app(GestionarJuradoSecretoService::class)->revelarJuradoSecreto($nusta, 1);

        $response = $this->get(route('inicio', ['categoria' => $nusta->id]));
        $response->assertStatus(200);
        $response->assertSee('Elección y Proclamación de la Ñusta');
        $response->assertSee('Ñusta del Festival');
        $response->assertSee('Lucía Méndez');
        $response->assertDontSee('Segundo Lugar');
        $response->assertDontSee('Tercer Lugar');
        $response->assertSee('Clasificación General');
        $response->assertSee('Camila Torres');
    }
}
