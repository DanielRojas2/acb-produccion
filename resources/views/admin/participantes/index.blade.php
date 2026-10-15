<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">Participantes</h2>
            <a href="{{ route('admin.participantes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow">
                + Registrar participante
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
        <form method="GET" action="{{ route('admin.participantes.index') }}" class="mb-4 flex gap-2 max-w-md">
            <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Buscar por nombre o pseudónimo…"
                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg">
                Buscar
            </button>
            @if ($busqueda !== '')
                <a href="{{ route('admin.participantes.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 self-center">Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Apellido paterno</th>
                        <th class="py-2 pr-4">Apellido materno</th>
                        <th class="py-2 pr-4">Pseudónimo</th>
                        <th class="py-2 pr-4">Inscripciones</th>
                        <th class="py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participantes as $participante)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-medium text-slate-800">
                                <div class="flex items-center gap-2.5">
                                    <div class="relative flex -space-x-2 shrink-0">
                                        @if ($participante->fotoUrl())
                                            <img src="{{ $participante->fotoUrl() }}" alt="Con fondo" title="Foto con fondo (Ranking)" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm">
                                        @endif
                                        @if ($participante->fotoSinFondoUrl())
                                            <img src="{{ $participante->fotoSinFondoUrl() }}" alt="Sin fondo" title="Imagen sin fondo PNG (Jurado)" class="w-8 h-8 rounded-full object-contain bg-slate-900 border-2 border-white shadow-sm p-0.5">
                                        @endif
                                        @if (! $participante->fotoUrl() && ! $participante->fotoSinFondoUrl())
                                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-bold">
                                                {{ mb_substr($participante->nombre, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span>{{ $participante->nombre }}</span>
                                </div>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $participante->paterno ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $participante->materno ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $participante->pseudonimo ?? '—' }}</td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $participante->inscripciones_count }}
                                </span>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <a href="{{ route('admin.participantes.edit', $participante) }}" class="text-indigo-600 hover:text-indigo-800 ml-2">Editar</a>
                                <form method="POST" action="{{ route('admin.participantes.destroy', $participante) }}" class="inline ml-2"
                                    onsubmit="return confirm('¿Eliminar a {{ addslashes($participante->nombreCompleto()) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Sin participantes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $participantes->links() }}
        </div>
    </div>
</x-layouts.admin>