<?php

use App\Support\Enums\RolEnum;
use Illuminate\Support\Facades\Broadcast;

/*
| Canales de tiempo real del festival.
| Se agregan aquí los canales que definirán cada fase:
|   - presentaciones        : estado y cronómetro de la presentación en curso
|   - presentaciones.{id}   : eventos de una presentación específica
|   - ranking               : actualización del ranking público
|   - calificaciones.{id}   : cambios de calificación de una presentación
*/

Broadcast::channel('App.Models.Usuario.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presentaciones', function ($user) {
    return $user->rol?->value === RolEnum::ADMIN->value || $user->rol?->value === RolEnum::JURADO->value;
});

Broadcast::channel('presentaciones.{id}', function ($user, $id) {
    return $user->rol?->value === RolEnum::ADMIN->value || $user->rol?->value === RolEnum::JURADO->value;
});

Broadcast::channel('ranking', function ($user) {
    return true;
});