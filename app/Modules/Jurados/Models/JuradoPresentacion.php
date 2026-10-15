<?php

namespace App\Modules\Jurados\Models;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JuradoPresentacion extends Model
{
    protected $table = 'jurado_presentacion';

    protected $fillable = [
        'id_presentacion',
        'id_usuario',
        'es_secreto',
        'es_controlador',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'es_secreto' => 'boolean',
            'es_controlador' => 'boolean',
            'estado' => JuradoPresentacionEstadoEnum::class,
        ];
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function calificacion(): HasOne
    {
        return $this->hasOne(Calificacion::class, 'id_jurado_presentacion');
    }
}