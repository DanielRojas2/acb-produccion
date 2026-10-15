<?php

namespace Tests\Unit\Categorias;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriasGanadoresReglaTest extends TestCase
{
    use RefreshDatabase;

    public function test_nusta_tiene_un_solo_ganador(): void
    {
        $nusta = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
        ]);

        $this->assertTrue($nusta->esNusta());
        $this->assertFalse($nusta->esEspecial());
        $this->assertTrue($nusta->tieneUnSoloGanador());
        $this->assertSame(1, $nusta->cantidadGanadores());
        $this->assertSame('Ñusta del Festival', $nusta->etiquetaGanador());
    }

    public function test_especial_tiene_un_solo_ganador(): void
    {
        $especial = Categoria::create([
            'categoria' => 'Especial',
            'max_participantes' => 12,
            'tiempo_presentacion' => 10,
        ]);

        $this->assertFalse($especial->esNusta());
        $this->assertTrue($especial->esEspecial());
        $this->assertTrue($especial->tieneUnSoloGanador());
        $this->assertSame(1, $especial->cantidadGanadores());
        $this->assertSame('Ganador Categoría Especial', $especial->etiquetaGanador());
    }

    public function test_otras_categorias_tienen_tres_ganadores_en_podio(): void
    {
        $otras = [
            'Solista vocal (con o sin acompañamiento)',
            'Solista instrumental – Armónico',
            'Solista instrumental – Melódico (con o sin acompañamiento)',
            'Dúo vocal instrumental',
            'Trío vocal instrumental',
            'Grupo vocal instrumental',
            'Estudiantina',
            'Autóctono',
            'Composición',
            'Solista vocal infantil (con o sin acompañamiento)',
            'Solista instrumental infantil (con o sin acompañamiento)',
        ];

        foreach ($otras as $nombre) {
            $cat = Categoria::create([
                'categoria' => $nombre,
                'max_participantes' => 6,
                'tiempo_presentacion' => 8,
            ]);

            $this->assertFalse($cat->tieneUnSoloGanador(), "La categoría {$nombre} no debe ser de un solo ganador.");
            $this->assertSame(3, $cat->cantidadGanadores(), "La categoría {$nombre} debe tener 3 ganadores.");
            $this->assertSame('Ganador Oficial', $cat->etiquetaGanador());
        }
    }

    public function test_revelacion_de_podio_para_categoria_de_un_solo_ganador(): void
    {
        $nusta = Categoria::create([
            'categoria' => 'Ñusta',
            'max_participantes' => 1,
            'tiempo_presentacion' => 10,
            'jurado_secreto_revelado' => true,
            'podio_revelado_hasta' => 0, // modo misterio
        ]);

        $this->assertFalse($nusta->esPodioLugarRevelado(1));
        $this->assertFalse($nusta->esPodioLugarRevelado(2));
        $this->assertFalse($nusta->esPodioLugarRevelado(3));

        // Al pasar a paso 1, solo el 1er lugar está revelado, 2 y 3 nunca
        $nusta->podio_revelado_hasta = 1;
        $this->assertTrue($nusta->esPodioLugarRevelado(1));
        $this->assertFalse($nusta->esPodioLugarRevelado(2));
        $this->assertFalse($nusta->esPodioLugarRevelado(3));
    }
}
