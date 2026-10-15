@php
    $usuario ??= null;
    $editableRol ??= true;
    $esSelf = $usuario?->id === auth()->id();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-semibold text-slate-800 mb-4">
        {{ isset($usuario) ? 'Editar usuario' : 'Nuevo usuario' }}
    </h3>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input id="nombre" class="mt-1 block w-full" type="text" name="nombre"
                :value="old('nombre', $usuario->nombre ?? null)" required autofocus />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="paterno" :value="__('Apellido paterno')" />
            <x-text-input id="paterno" class="mt-1 block w-full" type="text" name="paterno"
                :value="old('paterno', $usuario->paterno ?? null)" />
            <x-input-error :messages="$errors->get('paterno')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="materno" :value="__('Apellido materno')" />
            <x-text-input id="materno" class="mt-1 block w-full" type="text" name="materno"
                :value="old('materno', $usuario->materno ?? null)" />
            <x-input-error :messages="$errors->get('materno')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="ci" :value="__('C.I.')" />
            <x-text-input id="ci" class="mt-1 block w-full" type="text" name="ci"
                :value="old('ci', $usuario->ci ?? null)" />
            <x-input-error :messages="$errors->get('ci')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Nombre de usuario (opcional)')" />
            <x-text-input id="username" class="mt-1 block w-full" type="text" name="username"
                :value="old('username', $usuario->username ?? null)" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                :value="old('email', $usuario->email ?? null)" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="rol" :value="__('Rol')" />
            @if ($editableRol)
                <select id="rol" name="rol" required
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="{{ App\Support\Enums\RolEnum::JURADO->value }}" @selected(old('rol', $usuario->rol ?? null) === App\Support\Enums\RolEnum::JURADO->value)>
                        {{ App\Support\Enums\RolEnum::JURADO->label() }}
                    </option>
                    <option value="{{ App\Support\Enums\RolEnum::ADMIN->value }}" @selected(old('rol', $usuario->rol ?? null) === App\Support\Enums\RolEnum::ADMIN->value)>
                        {{ App\Support\Enums\RolEnum::ADMIN->label() }}
                    </option>
                </select>
            @else
                <input type="hidden" name="rol" value="{{ $usuario->rol->value }}">
                <p class="mt-1 text-sm text-slate-600 font-medium">{{ $usuario->rol->label() }}</p>
                <p class="text-xs text-slate-400">No puedes cambiar tu propio rol.</p>
            @endif
            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
        </div>

        <div class="md:pt-6">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="puede_controlar_tiempo" value="1"
                    @checked(old('puede_controlar_tiempo', $usuario?->puedeControlarTiempo() ?? false))
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-600">Puede controlar el cronómetro</span>
            </label>
            <x-input-error :messages="$errors->get('puede_controlar_tiempo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="isset($usuario) ? __('Nueva contraseña (opcional)') : __('Contraseña')" />
            <input id="password" type="password" name="password"
                {{ isset($usuario) ? '' : 'required' }} autocomplete="new-password"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @if (isset($usuario))
                <p class="mt-1 text-xs text-slate-400">Dejar en blanco para no cambiar.</p>
            @endif
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
            <input id="password_confirmation" type="password" name="password_confirmation"
                {{ isset($usuario) ? '' : 'required' }} autocomplete="new-password"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-md">
            Cancelar
        </a>
        <x-primary-button>
            {{ isset($usuario) ? __('Guardar cambios') : __('Crear usuario') }}
        </x-primary-button>
    </div>
</div>