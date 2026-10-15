<?php

namespace App\Modules\Inscripciones\Models;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\InscripcionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $fillable = [
        'fecha_inscripcion',
        'hora_presentacion',
        'fecha_presentacion',
        'id_fase',
        'id_categoria',
        'id_departamento',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'hora_presentacion' => 'immutable_datetime:H:i',
            'fecha_presentacion' => 'date',
            'estado' => InscripcionEstadoEnum::class,
        ];
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(Fase::class, 'id_fase');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Participante::class,
            'inscripcion_participante',
            'id_inscripcion',
            'id_participante'
        );
    }

    public function inscripcionParticipantes(): HasMany
    {
        return $this->hasMany(InscripcionParticipante::class, 'id_inscripcion');
    }

    public function presentacion(): HasOne
    {
        return $this->hasOne(Presentacion::class, 'id_inscripcion');
    }

    public function esPreseleccion(): bool
    {
        return $this->fase?->esPreseleccion() ?? false;
    }

    /**
     * Una inscripción solo puede editarse/eliminarse desde el panel si su
     * presentación aún no ha comenzado (estado POR_PARTICIPAR).
     */
    public function puedeAdministrarse(): bool
    {
        $presentacion = $this->presentacion;

        return $presentacion === null || $presentacion->estado === PresentacionEstadoEnum::POR_PARTICIPAR;
    }
}
