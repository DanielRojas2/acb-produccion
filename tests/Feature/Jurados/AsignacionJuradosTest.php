<?php

namespace Tests\Feature\Jurados;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class AsignacionJuradosTest extends TestCase
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
    public function un_admin_puede_asignar_jurados_manualmente(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $ids = array_slice(array_map(fn ($j) => $j->id, $jurados), 0, 3);

        $this->actingAs($admin)
            ->from(route('admin.jurados.index'))
            ->post(route('admin.jurados.asignar'), [
                'presentacion_id' => $presentacion->id,
                'usuario_ids' => $ids,
            ])
            ->assertRedirect(route('admin.jurados.index'))
            ->assertSessionHas('status');

        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());
    }

    #[Test]
    public function rechaza_una_cantidad_incorrecta_de_jurados(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $ids = array_slice(array_map(fn ($j) => $j->id, $jurados), 0, 2);

        $this->actingAs($admin)
            ->from(route('admin.jurados.index'))
            ->post(route('admin.jurados.asignar'), [
                'presentacion_id' => $presentacion->id,
                'usuario_ids' => $ids,
            ])
            ->assertRedirect(route('admin.jurados.index'))
            ->assertSessionHas('error');

        $this->assertSame(0, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());
    }

    #[Test]
    public function el_admin_puede_autoasignar_en_bloque(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        $this->crearJurados(5);

        $primera = $this->presentacionPreseleccion();
        $segunda = $this->presentacionFinal();

        $this->actingAs($admin)
            ->post(route('admin.jurados.auto-asignar'))
            ->assertSessionHas('status');

        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $primera->id)->count());
        $this->assertSame(5, JuradoPresentacion::where('id_presentacion', $segunda->id)->count());
        $this->assertSame(8, JuradoPresentacion::count());
    }

    #[Test]
    public function el_jurado_controlador_se_marca_al_autoasignar(): void
    {
        $admin = Usuario::factory()->administrador()->create();
        Usuario::factory()->create(['rol' => RolEnum::JURADO, 'puede_controlar_tiempo' => true]);
        $jurados = $this->crearJurados(2);

        $presentacion = $this->presentacionPreseleccion();

        $this->actingAs($admin)
            ->post(route('admin.jurados.auto-asignar'));

        $controladores = JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('es_controlador', true)
            ->count();

        $this->assertSame(1, $controladores);
    }

    #[Test]
    public function un_jurado_no_puede_acceder_a_la_asignacion(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $this->actingAs($jurado)
            ->get(route('admin.jurados.index'))
            ->assertForbidden();
    }
}