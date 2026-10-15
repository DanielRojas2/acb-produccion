<?php

namespace App\Support\Enums;

enum FaseEnum: string
{
    case PRESELECCION = 'PRESELECCION';
    case FINAL = 'FINAL';

    public function label(): string
    {
        return match ($this) {
            self::PRESELECCION => 'Preselección',
            self::FINAL => 'Final',
        };
    }

    public function esPreseleccion(): bool
    {
        return $this === self::PRESELECCION;
    }

    public function esFinal(): bool
    {
        return $this === self::FINAL;
    }
}
