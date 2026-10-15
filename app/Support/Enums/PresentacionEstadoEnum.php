<?php

namespace App\Support\Enums;

enum PresentacionEstadoEnum: string
{
    case POR_PARTICIPAR = 'POR_PARTICIPAR';
    case EN_ESCENARIO = 'EN_ESCENARIO';
    case PARTICIPANDO = 'PARTICIPANDO';
    case FINALIZADO = 'FINALIZADO';

    public function label(): string
    {
        return match ($this) {
            self::POR_PARTICIPAR => 'Por participar',
            self::EN_ESCENARIO => 'En escenario',
            self::PARTICIPANDO => 'Participando',
            self::FINALIZADO => 'Finalizado',
        };
    }

    public function esInicial(): bool
    {
        return $this === self::POR_PARTICIPAR;
    }

    public function esFinal(): bool
    {
        return $this === self::FINALIZADO;
    }

    public function puedeTransicionarA(self $siguiente): bool
    {
        return match ($this) {
            self::POR_PARTICIPAR => $siguiente === self::EN_ESCENARIO,
            self::EN_ESCENARIO => $siguiente === self::PARTICIPANDO,
            self::PARTICIPANDO => $siguiente === self::FINALIZADO,
            self::FINALIZADO => false,
        };
    }
}
