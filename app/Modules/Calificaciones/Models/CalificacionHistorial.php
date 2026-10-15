<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Usuarios\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionHistorial extends Model
{
    use HasFactory;

    protected $table = 'calificacion_historial';

    protected $fillable = [
        'id_calificacion',
        'id_usuario',
        'datos_anteriores',
        'datos_nuevos',
        'motivo',
        'fecha',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha' => 'datetime',
    ];

    public function calificacion(): BelongsTo
    {
        return $this->belongsTo(Calificacion::class, 'id_calificacion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
