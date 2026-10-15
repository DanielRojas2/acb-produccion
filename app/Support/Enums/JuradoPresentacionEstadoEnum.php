<?php

namespace App\Support\Enums;

enum JuradoPresentacionEstadoEnum: string
{
    case ASIGNADO = 'ASIGNADO';
    case CALIFICANDO = 'CALIFICANDO';
    case FINALIZADO = 'FINALIZADO';

    public function label(): string
    {
        return match ($this) {
            self::ASIGNADO => 'Asignado',
            self::CALIFICANDO => 'Calificando',
            self::FINALIZADO => 'Finalizado',
        };
    }
}