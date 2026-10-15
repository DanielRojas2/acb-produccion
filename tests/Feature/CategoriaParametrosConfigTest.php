<?php

namespace Tests\Feature;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Categorias\Models\CategoriaParametro;
use App\Modules\Parametros\Models\Parametro;
use Database\Seeders\CategoriaParametrosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaParametrosConfigTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function seed_crea_trece_categorias_con_suma_cien(): void
    {
        $this->seed(CategoriaParametrosSeeder::class);

        $this->assertSame(13, Categoria::count());
        $this->assertSame(47, CategoriaParametro::count());

        foreach (Categoria::with('categoriaParametros')->get() as $categoria) {
            $this->assertSame(
                100,
                $categoria->categoriaParametros->sum('valor_maximo'),
                "La categoría '{$categoria->categoria}' debe sumar 100."
            );
            $this->assertTrue($categoria->sumaEsCien());

            foreach ($categoria->categoriaParametros as $cp) {
                $this->assertTrue($cp->valor_minimo <= $cp->valor_maximo);
            }
        }
    }

    #[Test]
    public function seed_es_idempotente(): void
    {
        $this->seed(CategoriaParametrosSeeder::class);
        $this->seed(CategoriaParametrosSeeder::class);

        $this->assertSame(13, Categoria::count());
        $this->assertSame(20, Parametro::count());
        $this->assertSame(47, CategoriaParametro::count());
    }

    #[Test]
    public function no_se_pueden_duplicar_parametros_en_una_categoria(): void
    {
        $categoria = Categoria::create([
            'categoria' => 'Categoría de prueba',
            'max_participantes' => 1,
            'tiempo_presentacion' => 5,
        ]);

        $parametroA = Parametro::create(['parametro' => 'Parámetro A']);
        $parametroB = Parametro::create(['parametro' => 'Parámetro B']);

        CategoriaParametro::create([
            'id_categoria' => $categoria->id,
            'id_parametro' => $parametroA->id,
            'valor_minimo' => 1,
            'valor_maximo' => 50,
            'orden' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        CategoriaParametro::create([
            'id_categoria' => $categoria->id,
            'id_parametro' => $parametroA->id,
            'valor_minimo' => 1,
            'valor_maximo' => 60,
            'orden' => 2,
        ]);

        $this->assertCount(
            1,
            CategoriaParametro::where('id_categoria', $categoria->id)->get()
        );
    }
}