<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\RolEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UsuarioFactory> */
    use HasFactory, Notifiable;

    protected static function newFactory()
    {
        return \Database\Factories\UsuarioFactory::new();
    }

    protected $table = 'users';

    protected $fillable = [
        'username',
        'nombre',
        'paterno',
        'materno',
        'ci',
        'email',
        'password',
        'rol',
        'puede_controlar_tiempo',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => RolEnum::class,
            'puede_controlar_tiempo' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return $this->rol?->esAdmin() ?? false;
    }

    public function esJurado(): bool
    {
        return $this->rol?->esJurado() ?? false;
    }

    public function nombreCompleto(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->paterno,
            $this->materno,
        ]))) ?: ($this->username ?? 'Usuario');
    }

    public function puedeControlarTiempo(): bool
    {
        return $this->puede_controlar_tiempo;
    }

    public function presentacionesJurado(): BelongsToMany
    {
        return $this->belongsToMany(
            Presentacion::class,
            'jurado_presentacion',
            'id_usuario',
            'id_presentacion'
        )->withPivot(['es_secreto', 'es_controlador', 'estado']);
    }
}