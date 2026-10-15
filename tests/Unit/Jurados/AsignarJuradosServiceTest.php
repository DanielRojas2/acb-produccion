<?php

namespace Tests\Unit\Jurados;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Jurados\Services\AsignarJuradosService;
use App\Support\Enums\RolEnum;
use Database\Seeders\CategoriaParametrosSeeder;
use Database\Seeders\DepartamentoSeeder;
use Database\Seeders\FaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreaPresentacion;
use Tests\TestCase;

class AsignarJuradosServiceTest extends TestCase
{
    use CreaPresentacion, RefreshDatabase;

    private AsignarJuradosService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartamentoSeeder::class,
            FaseSeeder::class,
            CategoriaParametrosSeeder::class,
        ]);

        $this->servicio = new AsignarJuradosService();
    }

    #[Test]
    public function la_preseleccion_requiere_tres_jurados(): void
    {
        $this->assertSame(3, $this->servicio->cantidadRequerida($this->presentacionPreseleccion()));
    }

    #[Test]
    public function la_final_requiere_cinco_jurados(): void
    {
        $this->assertSame(5, $this->servicio->cantidadRequerida($this->presentacionFinal()));
    }

    #[Test]
    public function autoasignar_completa_los_puestos_con_jurados_disponibles(): void
    {
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $creadas = $this->servicio->autoAsignar($presentacion);

        $this->assertCount(3, $creadas);
        $this->assertTrue($this->servicio->estaCompleta($presentacion));
        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());

        $ids = array_map(fn ($j) => $j->id_usuario, $creadas);
        $todosJuradosIds = array_map(fn ($j) => $j->id, $jurados);

        foreach ($ids as $id) {
            $this->assertContains($id, $todosJuradosIds);
        }
    }

    #[Test]
    public function autoasignar_no_duplica_ni_excede_la_cantidad(): void
    {
        $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $this->servicio->autoAsignar($presentacion);
        $segunda = $this->servicio->autoAsignar($presentacion->refresh());

        $this->assertSame([], $segunda);
        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());
    }

    #[Test]
    public function autoasignar_falla_cuando_no_hay_suficientes_jurados(): void
    {
        $this->crearJurados(2);
        $presentacion = $this->presentacionPreseleccion();

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->autoAsignar($presentacion);
    }

    #[Test]
    public function asignar_manual_reemplaza_la_lista_completa(): void
    {
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $ids = array_slice(array_map(fn ($j) => $j->id, $jurados), 0, 3);

        $this->servicio->asignar($presentacion, $ids);

        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());
        $this->assertSame($ids, JuradoPresentacion::where('id_presentacion', $presentacion->id)->orderBy('id_usuario')->pluck('id_usuario')->all());
    }

    #[Test]
    public function asignar_manual_rechaza_una_cantidad_incorrecta(): void
    {
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $ids = array_slice(array_map(fn ($j) => $j->id, $jurados), 0, 2);

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->asignar($presentacion, $ids);
    }

    #[Test]
    public function asignar_manual_rechaza_usuarios_que_no_son_jurados(): void
    {
        $jurado = \App\Modules\Usuarios\Models\Usuario::factory()->administrador()->create();
        $otros = $this->crearJurados(2);
        $presentacion = $this->presentacionPreseleccion();

        $ids = [$jurado->id, ...array_map(fn ($j) => $j->id, $otros)];

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->asignar($presentacion, $ids);
    }

    #[Test]
    public function agregar_jurado_cuando_aun_no_esta_completa(): void
    {
        $jurados = $this->crearJurados(3);
        $presentacion = $this->presentacionPreseleccion();

        $this->servicio->agregarJurado($presentacion, $jurados[0]->id);
        $this->servicio->agregarJurado($presentacion, $jurados[1]->id);

        $this->assertSame(2, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());

        $this->servicio->agregarJurado($presentacion, $jurados[2]->id);

        $this->assertSame(3, JuradoPresentacion::where('id_presentacion', $presentacion->id)->count());

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->agregarJurado($presentacion, $jurados[0]->id);
    }

    #[Test]
    public function remover_no_baja_de_la_cantidad_requerida(): void
    {
        $jurados = $this->crearJurados(5);
        $presentacion = $this->presentacionPreseleccion();

        $ids = array_slice(array_map(fn ($j) => $j->id, $jurados), 0, 3);
        $this->servicio->asignar($presentacion, $ids);

        $this->expectException(\UnexpectedValueException::class);

        $this->servicio->removerJurado($presentacion, $ids[0]);
    }

    #[Test]
    public function el_jurado_con_control_de_tiempo_se_marca_como_controlador(): void
    {
        $jurado = \App\Modules\Usuarios\Models\Usuario::factory()->create([
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => true,
        ]);

        $presentacion = $this->presentacionPreseleccion();

        $this->servicio->agregarJurado($presentacion, $jurado->id);

        $this->assertTrue(JuradoPresentacion::where('id_presentacion', $presentacion->id)->first()->es_controlador);
    }
}