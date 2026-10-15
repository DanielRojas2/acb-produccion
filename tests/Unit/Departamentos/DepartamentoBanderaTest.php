<?php

namespace Tests\Unit\Departamentos;

use App\Modules\Departamentos\Models\Departamento;
use Database\Seeders\DepartamentoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartamentoBanderaTest extends TestCase
{
    use RefreshDatabase;

    public function test_departamento_seeder_asigna_banderas_oficiales(): void
    {
        $this->seed(DepartamentoSeeder::class);

        $this->assertDatabaseCount('departamentos', 9);

        $esperados = [
            'Chuquisaca' => 'chuquisaca.png',
            'La Paz' => 'lapaz.png',
            'Cochabamba' => 'cochabamba.png',
            'Oruro' => 'oruro.png',
            'Potosí' => 'potosi.png',
            'Tarija' => 'tarija.png',
            'Santa Cruz' => 'santacruz.png',
            'Beni' => 'beni.png',
            'Pando' => 'pando.png',
        ];

        foreach ($esperados as $nombre => $bandera) {
            $dep = Departamento::where('nombre', $nombre)->first();
            $this->assertNotNull($dep, "El departamento {$nombre} debe existir.");
            $this->assertSame($bandera, $dep->bandera);
            $this->assertStringContainsString("images/banderas/{$bandera}", $dep->bandera_url);
        }
    }

    public function test_resolucion_estatica_de_banderas_por_nombre(): void
    {
        $this->assertSame('oruro.png', Departamento::archivoBanderaPorNombre('Oruro'));
        $this->assertSame('lapaz.png', Departamento::archivoBanderaPorNombre('La Paz'));
        $this->assertSame('lapaz.png', Departamento::archivoBanderaPorNombre('la paz'));
        $this->assertSame('potosi.png', Departamento::archivoBanderaPorNombre('Potosí'));
        $this->assertSame('potosi.png', Departamento::archivoBanderaPorNombre('potosi'));
        $this->assertSame('santacruz.png', Departamento::archivoBanderaPorNombre('Santa Cruz'));
        $this->assertNull(Departamento::archivoBanderaPorNombre('Desconocido'));
        $this->assertNull(Departamento::archivoBanderaPorNombre(null));

        $this->assertStringContainsString('images/banderas/oruro.png', Departamento::urlBanderaPorNombre('Oruro'));
        $this->assertNull(Departamento::urlBanderaPorNombre('Inexistente'));
    }

    public function test_accessor_bandera_url_con_campo_o_fallback(): void
    {
        $depConBandera = Departamento::create([
            'nombre' => 'Oruro',
            'bandera' => 'oruro.png',
        ]);
        $this->assertStringContainsString('images/banderas/oruro.png', $depConBandera->bandera_url);

        $depSinBandera = Departamento::create([
            'nombre' => 'Cochabamba',
            'bandera' => null,
        ]);
        $this->assertStringContainsString('images/banderas/cochabamba.png', $depSinBandera->bandera_url);

        $depOtro = Departamento::create([
            'nombre' => 'Exterior',
            'bandera' => null,
        ]);
        $this->assertNull($depOtro->bandera_url);
    }
}
