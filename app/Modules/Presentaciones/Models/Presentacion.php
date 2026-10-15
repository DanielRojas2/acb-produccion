<?php

namespace App\Modules\Presentaciones\Models;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Presentacion extends Model
{
    protected $table = 'presentaciones';

    protected $fillable = [
        'id_inscripcion',
        'nombre_presentacion',
        'foto',
        'foto_sin_fondo',
        'estado',
        'tiempo_maximo',
        'hora_inicio',
        'hora_fin',
        'cronometro_iniciado_at',
        'cronometro_finalizado_at',
        'nota_final',
    ];

    protected function casts(): array
    {
        return [
            'estado' => PresentacionEstadoEnum::class,
            'tiempo_maximo' => 'integer',
            'hora_inicio' => 'immutable_datetime:H:i',
            'hora_fin' => 'immutable_datetime:H:i',
            'cronometro_iniciado_at' => 'datetime',
            'cronometro_finalizado_at' => 'datetime',
            'nota_final' => 'decimal:2',
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function jurados(): HasMany
    {
        return $this->hasMany(JuradoPresentacion::class, 'id_presentacion');
    }

    public function juradosPresentacion(): HasMany
    {
        return $this->jurados();
    }

    public function juradosAsignados(): HasMany
    {
        return $this->jurados()->with(['usuario', 'calificacion.detalles']);
    }

    public function calificaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Calificacion::class, JuradoPresentacion::class, 'id_presentacion', 'id_jurado_presentacion');
    }

    public function nombreMostrado(): string
    {
        return $this->nombre_presentacion
            ?? $this->inscripcion?->participantes()->first()?->nombreCompleto()
            ?? 'Sin nombre';
    }

    public function fotoUrl(): ?string
    {
        if ($this->foto) {
            return str_starts_with($this->foto, 'http')
                ? $this->foto
                : asset('storage/'.$this->foto);
        }

        return $this->inscripcion?->participantes->first()?->fotoUrl();
    }

    public function fotoSinFondoUrl(): ?string
    {
        if ($this->foto_sin_fondo) {
            return str_starts_with($this->foto_sin_fondo, 'http')
                ? $this->foto_sin_fondo
                : asset('storage/'.$this->foto_sin_fondo);
        }

        return $this->inscripcion?->participantes->first()?->fotoSinFondoUrl();
    }

    public function fotoJuradoUrl(): ?string
    {
        return $this->fotoSinFondoUrl() ?: $this->fotoUrl();
    }

    public function esPreseleccion(): bool
    {
        return $this->inscripcion?->esPreseleccion() ?? false;
    }

    public function esFinal(): bool
    {
        return $this->inscripcion?->fase?->esFinal() ?? false;
    }

    public function cronometroCorriendo(): bool
    {
        return $this->cronometro_iniciado_at !== null && $this->cronometro_finalizado_at === null;
    }

    public function segundosTranscurridos(): int
    {
        if ($this->cronometro_iniciado_at === null) {
            return 0;
        }

        $fin = $this->cronometro_finalizado_at ?? now();

        return max(0, (int) $this->cronometro_iniciado_at->diffInSeconds($fin));
    }

    public function segundosRestantes(): int
    {
        return max(0, $this->tiempo_maximo * 60 - $this->segundosTranscurridos());
    }
}
