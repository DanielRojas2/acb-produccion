<?php

namespace App\Modules\Inscripciones\Models;

use Illuminate\Database\Eloquent\Model;

class InscripcionParticipante extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'inscripcion_participante';

    protected $fillable = ['id_participante', 'id_inscripcion'];
}
