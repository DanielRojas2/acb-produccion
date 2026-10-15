<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">Usuarios</h2>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow">
                + Nuevo usuario
            </a>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mb-4 flex gap-2 max-w-md">
            <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Buscar por nombre, email, C.I.…"
                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg">
                Buscar
            </button>
            @if ($busqueda !== '')
                <a href="{{ route('admin.usuarios.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 self-center">Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Usuario</th>
                        <th class="py-2 pr-4">Username</th>
                        <th class="py-2 pr-4">C.I.</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Rol</th>
                        <th class="py-2 pr-4">Cronómetro</th>
                        <th class="py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4">
                                <span class="font-medium text-slate-800">{{ $usuario->nombre }} {{ $usuario->paterno }} {{ $usuario->materno }}</span>
                                @if ($usuario->id === auth()->id())
                                    <span class="ml-1 text-xs text-slate-400">(tú)</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $usuario->username ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $usuario->ci ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $usuario->email }}</td>
                            <td class="py-2 pr-4">
                                <span @class(['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-indigo-100 text-indigo-700' => $usuario->esAdmin(), 'bg-amber-100 text-amber-700' => $usuario->esJurado()])>
                                    {{ $usuario->rol->label() }}
                                </span>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $usuario->puedeControlarTiempo() ? 'Sí' : 'No' }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="text-indigo-600 hover:text-indigo-800 ml-2">Editar</a>
                                @if ($usuario->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" class="inline ml-2"
                                        onsubmit="return confirm('¿Eliminar a {{ addslashes($usuario->nombre) }} {{ addslashes($usuario->paterno ?? '') }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Sin usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
    </div>
</x-layouts.admin>