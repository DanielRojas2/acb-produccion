<?php

namespace Database\Factories;

use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\RolEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'nombre' => fake()->firstName(),
            'paterno' => fake()->lastName(),
            'materno' => fake()->lastName(),
            'ci' => (string) fake()->randomNumber(7),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => RolEnum::JURADO,
            'puede_controlar_tiempo' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => RolEnum::ADMIN,
        ]);
    }
}