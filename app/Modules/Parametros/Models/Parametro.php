<?php

namespace App\Modules\Parametros\Models;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Parametro extends Model
{
    public $timestamps = false;

    protected $fillable = ['parametro'];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_parametro', 'id_parametro', 'id_categoria')
            ->withPivot(['valor_minimo', 'valor_maximo', 'orden']);
    }
}
