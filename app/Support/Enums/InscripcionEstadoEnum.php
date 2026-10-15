<?php

namespace App\Support\Enums;

enum InscripcionEstadoEnum: string
{
    case REGISTRADA = 'REGISTRADA';
    case CONFIRMADA = 'CONFIRMADA';
    case CANCELADA = 'CANCELADA';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRADA => 'Registrada',
            self::CONFIRMADA => 'Confirmada',
            self::CANCELADA => 'Cancelada',
        };
    }
}
