<?php

namespace Tests\Unit\Categorias;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Services\RegistrarInscripcionService;
use App\Support\Enums\FaseEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiempoPresentacionNustaTest extends TestCase
{
    use RefreshDatabase;

    public function test_categoria_nusta_tiempo_por_fase(): void
    {
        $nusta = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
        ]);

        $this->assertTrue($nusta->esNusta());
        $this->assertSame(10, $nusta->tiempoParaFase(FaseEnum::PRESELECCION));
        $this->assertSame(15, $nusta->tiempoParaFase(FaseEnum::FINAL));
        $this->assertSame(10, $nusta->tiempoParaFase(null));
    }

    public function test_otra_categoria_mantiene_mismo_tiempo_en_ambas_fases(): void
    {
        $solista = Categoria::create([
            'categoria' => 'Solista vocal (con o sin acompañamiento)',
            'max_participantes' => 6,
            'tiempo_presentacion' => 5,
        ]);

        $this->assertFalse($solista->esNusta());
        $this->assertSame(5, $solista->tiempoParaFase(FaseEnum::PRESELECCION));
        $this->assertSame(5, $solista->tiempoParaFase(FaseEnum::FINAL));
    }

    public function test_registrar_inscripcion_asigna_tiempo_correcto_para_nusta(): void
    {
        $nusta = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
        ]);

        $oruro = Departamento::create(['nombre' => 'Oruro']);
        $fasePre = Fase::create(['fase' => FaseEnum::PRESELECCION->value, 'departamento_id' => $oruro->id]);
        $faseFin = Fase::create(['fase' => FaseEnum::FINAL->value]);

        $service = app(RegistrarInscripcionService::class);

        // Preselección: 10 minutos
        $inscPre = $service->registrar([
            'categoria' => 'Ñusta',
            'fase' => FaseEnum::PRESELECCION->value,
            'departamento' => 'Oruro',
            'fecha_inscripcion' => '2026-09-15',
            'fecha_presentacion' => '2026-09-20',
            'hora_presentacion' => '19:00',
            'nombre_presentacion' => 'Candidata Pre',
            'integrantes' => [
                ['nombre' => 'María', 'paterno' => 'Flores', 'materno' => null],
            ],
        ]);

        $this->assertSame(10, $inscPre->presentacion->tiempo_maximo);

        // Final: 15 minutos
        $inscFin = $service->registrar([
            'categoria' => 'Ñusta',
            'fase' => FaseEnum::FINAL->value,
            'departamento' => null,
            'fecha_inscripcion' => '2026-11-10',
            'fecha_presentacion' => '2026-11-20',
            'hora_presentacion' => '20:00',
            'nombre_presentacion' => 'Candidata Final',
            'integrantes' => [
                ['nombre' => 'Lucía', 'paterno' => 'Morales', 'materno' => null],
            ],
        ]);

        $this->assertSame(15, $inscFin->presentacion->tiempo_maximo);
    }
}
