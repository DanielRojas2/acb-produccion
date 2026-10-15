<?php

namespace App\Support\Enums;

enum CalificacionEstadoEnum: string
{
    case BORRADOR = 'BORRADOR';
    case EMITIDO = 'EMITIDO';
    case CORREGIDO = 'CORREGIDO';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::EMITIDO => 'Emitido',
            self::CORREGIDO => 'Corregido',
        };
    }
}
