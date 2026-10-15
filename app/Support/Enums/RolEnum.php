<?php

namespace App\Support\Enums;

enum RolEnum: string
{
    case ADMIN = 'ADMIN';
    case JURADO = 'JURADO';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::JURADO => 'Jurado',
        };
    }

    public function esAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function esJurado(): bool
    {
        return $this === self::JURADO;
    }
}
