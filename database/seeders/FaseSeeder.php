<?php

namespace Database\Seeders;

use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Support\Enums\FaseEnum;
use Illuminate\Database\Seeder;

class FaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Departamento::all() as $departamento) {
            Fase::firstOrCreate([
                'fase' => FaseEnum::PRESELECCION->value,
                'departamento_id' => $departamento->id,
            ]);
        }

        Fase::firstOrCreate([
            'fase' => FaseEnum::FINAL->value,
            'departamento_id' => null,
        ]);
    }
}