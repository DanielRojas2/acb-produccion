<?php

namespace Tests\Unit\Calificaciones;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Services\CalcularNotaService;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\FaseEnum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class CalcularNotaServiceTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    protected CalcularNotaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->service = app(CalcularNotaService::class);
    }

    public function test_suma_correctamente_los_puntos_de_parametros_sin_multiplicador_porcentual(): void
    {
        // Categoría Ñusta:
        // 1. Interpretación vocal (30) -> 25
        // 2. Interpretación instrumental (40) -> 36
        // 3. Carisma, dominio de escenario y conocimientos culturales (15) -> 12
        // 4. Indumentaria representativa del lugar de origen (15) -> 14
        // Total esperado = 25 + 36 + 12 + 14 = 87 puntos.
        $categoria = Categoria::where('categoria', 'Ñusta')->firstOrFail();
        $params = $categoria->categoriaParametros()->orderBy('orden')->get();

        $valores = [
            $params[0]->id_parametro => 25,
            $params[1]->id_parametro => 36,
            $params[2]->id_parametro => 12,
            $params[3]->id_parametro => 14,
        ];

        $nota = $this->service->calcularNotaJurado($categoria, $valores);

        $this->assertSame(87.0, $nota);
    }

    public function test_rechaza_calificacion_si_falta_un_parametro(): void
    {
        $categoria = Categoria::where('categoria', 'Ñusta')->firstOrFail();
        $params = $categoria->categoriaParametros()->orderBy('orden')->get();

        $valores = [
            $params[0]->id_parametro => 25,
            $params[1]->id_parametro => 36,
            // Falta parametro 2 y 3
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Faltan parámetros requeridos');

        $this->service->calcularNotaJurado($categoria, $valores);
    }

    public function test_rechaza_valor_menor_al_minimo_permitido(): void
    {
        $categoria = Categoria::where('categoria', 'Ñusta')->firstOrFail();
        $params = $categoria->categoriaParametros()->orderBy('orden')->get();

        $valores = [
            $params[0]->id_parametro => 0, // Mínimo es 1
            $params[1]->id_parametro => 30,
            $params[2]->id_parametro => 10,
            $params[3]->id_parametro => 10,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('fuera del rango permitido');

        $this->service->calcularNotaJurado($categoria, $valores);
    }

    public function test_rechaza_valor_mayor_al_maximo_permitido(): void
    {
        $categoria = Categoria::where('categoria', 'Ñusta')->firstOrFail();
        $params = $categoria->categoriaParametros()->orderBy('orden')->get();

        $valores = [
            $params[0]->id_parametro => 35, // Máximo es 30
            $params[1]->id_parametro => 30,
            $params[2]->id_parametro => 10,
            $params[3]->id_parametro => 10,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('fuera del rango permitido');

        $this->service->calcularNotaJurado($categoria, $valores);
    }

    public function test_calcula_nota_final_promedio_con_tres_jurados_en_preseleccion(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $jurados = $this->crearJurados(3);

        $notas = [80.0, 90.0, 85.0]; // Promedio = 85.00

        foreach ($jurados as $index => $jurado) {
            $jp = JuradoPresentacion::create([
                'id_presentacion' => $presentacion->id,
                'id_usuario' => $jurado->id,
            ]);

            Calificacion::create([
                'id_jurado_presentacion' => $jp->id,
                'nota_total' => $notas[$index],
                'estado' => CalificacionEstadoEnum::EMITIDO,
            ]);
        }

        $notaFinal = $this->service->calcularNotaFinalPresentacion($presentacion);

        $this->assertSame(85.0, $notaFinal);
    }

    public function test_calcula_nota_final_promedio_con_cinco_jurados_en_etapa_final_incluyendo_secreto(): void
    {
        $presentacion = $this->presentacionFinal();
        $jurados = $this->crearJurados(5);

        $notas = [90.0, 80.0, 85.0, 95.0, 100.0]; // Suma = 450, Promedio = 90.00

        foreach ($jurados as $index => $jurado) {
            $jp = JuradoPresentacion::create([
                'id_presentacion' => $presentacion->id,
                'id_usuario' => $jurado->id,
                'es_secreto' => $index === 4, // El quinto jurado es secreto
            ]);

            Calificacion::create([
                'id_jurado_presentacion' => $jp->id,
                'nota_total' => $notas[$index],
                'estado' => CalificacionEstadoEnum::EMITIDO,
            ]);
        }

        $notaFinal = $this->service->calcularNotaFinalPresentacion($presentacion);

        // El jurado secreto SÍ participa del cálculo matemático
        $this->assertSame(90.0, $notaFinal);
    }

    public function test_retorna_null_si_no_hay_calificaciones_emitidas(): void
    {
        $presentacion = $this->presentacionPreseleccion();
        $this->assertNull($this->service->calcularNotaFinalPresentacion($presentacion));
    }
}
