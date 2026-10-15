<?php

namespace App\Modules\Participantes\Models;

use App\Modules\Inscripciones\Models\Inscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Participante extends Model
{
    protected $fillable = ['nombre', 'paterno', 'materno', 'pseudonimo', 'foto', 'foto_sin_fondo'];

    public function inscripciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Inscripcion::class,
            'inscripcion_participante',
            'id_participante',
            'id_inscripcion'
        );
    }

    public function nombreCompleto(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->paterno,
            $this->materno,
        ])));
    }

    public function fotoUrl(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return str_starts_with($this->foto, 'http')
            ? $this->foto
            : asset('storage/'.$this->foto);
    }

    public function fotoSinFondoUrl(): ?string
    {
        if (! $this->foto_sin_fondo) {
            return null;
        }

        return str_starts_with($this->foto_sin_fondo, 'http')
            ? $this->foto_sin_fondo
            : asset('storage/'.$this->foto_sin_fondo);
    }

    public function fotoJuradoUrl(): ?string
    {
        return $this->fotoSinFondoUrl() ?: $this->fotoUrl();
    }
}
