<?php

namespace App\Modules\Calificaciones\Models;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Support\Enums\CalificacionEstadoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'id_jurado_presentacion',
        'nota_total',
        'estado',
        'fecha_inicio',
        'fecha_finalizacion',
        'fecha_modificacion',
    ];

    protected $casts = [
        'nota_total' => 'float',
        'estado' => CalificacionEstadoEnum::class,
        'fecha_inicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    public function juradoPresentacion(): BelongsTo
    {
        return $this->belongsTo(JuradoPresentacion::class, 'id_jurado_presentacion');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCalificacion::class, 'id_calificacion');
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(CalificacionHistorial::class, 'id_calificacion')->orderByDesc('fecha');
    }

    public function esEmitida(): bool
    {
        return in_array($this->estado, [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO], true);
    }
}
