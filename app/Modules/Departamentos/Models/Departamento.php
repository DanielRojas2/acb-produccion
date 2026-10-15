<?php

namespace App\Modules\Departamentos\Models;

use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    public $timestamps = false;

    protected $table = 'departamentos';

    protected $fillable = ['nombre', 'bandera'];

    public static function archivoBanderaPorNombre(?string $nombre): ?string
    {
        if (empty($nombre)) {
            return null;
        }

        $normalizado = mb_strtolower(trim($nombre), 'UTF-8');
        $normalizado = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '],
            ['a', 'e', 'i', 'o', 'u', 'n', ''],
            $normalizado
        );

        $map = [
            'chuquisaca' => 'chuquisaca.png',
            'lapaz' => 'lapaz.png',
            'cochabamba' => 'cochabamba.png',
            'oruro' => 'oruro.png',
            'potosi' => 'potosi.png',
            'tarija' => 'tarija.png',
            'santacruz' => 'santacruz.png',
            'beni' => 'beni.png',
            'pando' => 'pando.png',
        ];

        return $map[$normalizado] ?? null;
    }

    public static function urlBanderaPorNombre(?string $nombre): ?string
    {
        $archivo = static::archivoBanderaPorNombre($nombre);

        return $archivo ? asset('images/banderas/' . $archivo) : null;
    }

    public function getBanderaUrlAttribute(): ?string
    {
        if ($this->bandera) {
            return asset('images/banderas/' . $this->bandera);
        }

        return static::urlBanderaPorNombre($this->nombre);
    }

    public function fases(): HasMany
    {
        return $this->hasMany(Fase::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'id_departamento');
    }
}
