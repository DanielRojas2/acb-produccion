<?php

namespace App\Modules\Categorias\Models;

use App\Modules\Parametros\Models\Parametro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoriaParametro extends Model
{
    public $timestamps = false;

    protected $table = 'categoria_parametro';

    public $incrementing = false;

    protected $fillable = ['id_categoria', 'id_parametro', 'valor_minimo', 'valor_maximo', 'orden'];

    protected $casts = [
        'valor_minimo' => 'integer',
        'valor_maximo' => 'integer',
        'orden' => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function parametro(): BelongsTo
    {
        return $this->belongsTo(Parametro::class, 'id_parametro');
    }
}
