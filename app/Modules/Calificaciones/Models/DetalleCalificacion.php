<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Parametros\Models\Parametro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCalificacion extends Model
{
    use HasFactory;

    protected $table = 'detalle_calificaciones';

    protected $fillable = [
        'id_calificacion',
        'id_parametro',
        'valor',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function calificacion(): BelongsTo
    {
        return $this->belongsTo(Calificacion::class, 'id_calificacion');
    }

    public function parametro(): BelongsTo
    {
        return $this->belongsTo(Parametro::class, 'id_parametro');
    }
}
