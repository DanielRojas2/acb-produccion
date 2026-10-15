<?php

namespace Database\Seeders;

use App\Modules\Departamentos\Models\Departamento;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
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

        foreach ($departamentos as $nombre => $bandera) {
            Departamento::updateOrCreate(
                ['nombre' => $nombre],
                ['bandera' => $bandera]
            );
        }
    }
}