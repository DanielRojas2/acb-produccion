<?php

namespace App\Modules\Fases\Models;

use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Support\Enums\FaseEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fase extends Model
{
    public $timestamps = false;

    protected $fillable = ['fase', 'departamento_id'];

    protected $casts = [
        'fase' => FaseEnum::class,
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function esPreseleccion(): bool
    {
        return $this->fase === FaseEnum::PRESELECCION;
    }

    public function esFinal(): bool
    {
        return $this->fase === FaseEnum::FINAL;
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'id_fase');
    }
}
