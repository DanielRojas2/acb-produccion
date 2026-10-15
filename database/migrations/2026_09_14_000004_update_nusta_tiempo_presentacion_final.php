<?php

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\FaseEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizar presentaciones existentes de la categoría Ñusta en etapa final a 15 minutos
        $categoriaNusta = Categoria::where('categoria', 'like', '%Ñusta%')->first();

        if ($categoriaNusta) {
            Presentacion::whereHas('inscripcion', function ($query) use ($categoriaNusta) {
                $query->where('id_categoria', $categoriaNusta->id)
                    ->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value));
            })->update(['tiempo_maximo' => 15]);
        }
    }

    public function down(): void
    {
        $categoriaNusta = Categoria::where('categoria', 'like', '%Ñusta%')->first();

        if ($categoriaNusta) {
            Presentacion::whereHas('inscripcion', function ($query) use ($categoriaNusta) {
                $query->where('id_categoria', $categoriaNusta->id)
                    ->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value));
            })->update(['tiempo_maximo' => 10]);
        }
    }
};
