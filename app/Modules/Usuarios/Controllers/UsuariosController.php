<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\RolEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuariosController
{
    public function index(Request $request): View
    {
        $busqueda = $request->string('busqueda')->trim()->toString();

        $usuarios = Usuario::query()
            ->when($busqueda !== '', fn ($q) => $q->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('paterno', 'like', "%{$busqueda}%")
                    ->orWhere('username', 'like', "%{$busqueda}%")
                    ->orWhere('email', 'like', "%{$busqueda}%")
                    ->orWhere('ci', 'like', "%{$busqueda}%");
            }))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request, crear: true);

        Usuario::create([
            ...$datos,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.edit', ['usuario' => $usuario]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $datos = $this->validar($request, crear: false, usuario: $usuario);

        $usuario->fill($datos);
        $usuario->email_verified_at = now();
        $usuario->save();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_unless($usuario->id !== $request->user()->id, 403, 'No puedes eliminar tu propia cuenta.');

        if ($usuario->esAdmin() && Usuario::where('rol', RolEnum::ADMIN->value)->where('id', '!=', $usuario->id)->doesntExist()) {
            abort(403, 'Debe existir al menos un administrador.');
        }

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario eliminado.');
    }

    /**
     * @throws ValidationException
     */
    protected function validar(Request $request, bool $crear, ?Usuario $usuario = null): array
    {
        $id = $usuario?->id;

        $datos = $request->validate([
            'username' => ['nullable', 'string', 'max:60', Rule::unique('users', 'username')->ignore($id)],
            'nombre' => ['required', 'string', 'max:255'],
            'paterno' => ['nullable', 'string', 'max:255'],
            'materno' => ['nullable', 'string', 'max:255'],
            'ci' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'rol' => ['required', Rule::enum(RolEnum::class)],
            'puede_controlar_tiempo' => ['sometimes', 'boolean'],
            'password' => $crear
                ? ['required', 'confirmed', Rules\Password::defaults()]
                : ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $datos['username'] = blank($datos['username'] ?? null) ? null : $datos['username'];
        $datos['ci'] = blank($datos['ci'] ?? null) ? null : $datos['ci'];
        $datos['puede_controlar_tiempo'] = $request->boolean('puede_controlar_tiempo');

        if ($crear === false) {
            if (blank($request->input('password'))) {
                unset($datos['password']);
            }

            // Protección: el administrador no puede cambiar su propio rol.
            if ($usuario->id === $request->user()->id && $datos['rol'] !== $request->user()->rol?->value) {
                throw ValidationException::withMessages([
                    'rol' => 'No puedes cambiar tu propio rol.',
                ]);
            }

            // Protección: no se puede degradar al último administrador.
            if ($usuario->esAdmin()
                && $datos['rol'] !== RolEnum::ADMIN->value
                && Usuario::where('rol', RolEnum::ADMIN->value)->where('id', '!=', $usuario->id)->doesntExist()
            ) {
                throw ValidationException::withMessages([
                    'rol' => 'Debe existir al menos un administrador.',
                ]);
            }
        }

        return $datos;
    }
}