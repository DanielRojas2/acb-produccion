<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">Inscripciones</h2>
            <a href="{{ route('admin.inscripciones.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow">
                + Registrar inscripción
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
        <form method="GET" action="{{ route('admin.inscripciones.index') }}" class="mb-4 grid gap-3 md:grid-cols-[1fr_12rem_12rem_auto] items-end">
            <div>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Buscar por presentación o integrante…"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <select name="categoria" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->categoria }}" @selected($categoriaSeleccionada === $categoria->categoria)>
                            {{ $categoria->categoria }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <select name="fase" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Fase</option>
                    <option value="{{ App\Support\Enums\FaseEnum::PRESELECCION->value }}" @selected($faseSeleccionada === App\Support\Enums\FaseEnum::PRESELECCION->value)>
                        {{ App\Support\Enums\FaseEnum::PRESELECCION->label() }}
                    </option>
                    <option value="{{ App\Support\Enums\FaseEnum::FINAL->value }}" @selected($faseSeleccionada === App\Support\Enums\FaseEnum::FINAL->value)>
                        {{ App\Support\Enums\FaseEnum::FINAL->label() }}
                    </option>
                </select>
                <select name="departamento" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Depto.</option>
                    @foreach ($departamentos as $departamento)
                        <option value="{{ $departamento->nombre }}" @selected($departamentoSeleccionado === $departamento->nombre)>
                            {{ $departamento->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg">
                Filtrar
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Presentación</th>
                        <th class="py-2 pr-4">Categoría</th>
                        <th class="py-2 pr-4">Fase</th>
                        <th class="py-2 pr-4">Integrantes</th>
                        <th class="py-2 pr-4">Estado</th>
                        <th class="py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscripciones as $inscripcion)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-medium text-slate-800">
                                @php
                                    $fotoThumb = $inscripcion->presentacion?->fotoUrl() ?? $inscripcion->participantes->first()?->fotoUrl();
                                    $fotoSinFondoThumb = $inscripcion->presentacion?->fotoSinFondoUrl() ?? $inscripcion->participantes->first()?->fotoSinFondoUrl();
                                @endphp
                                <div class="flex items-center gap-2.5">
                                    <div class="relative flex -space-x-2 shrink-0">
                                        @if ($fotoThumb)
                                            <img src="{{ $fotoThumb }}" alt="Con fondo" title="Foto con fondo (Ranking)" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm">
                                        @endif
                                        @if ($fotoSinFondoThumb)
                                            <img src="{{ $fotoSinFondoThumb }}" alt="Sin fondo" title="Imagen sin fondo PNG (Jurado)" class="w-8 h-8 rounded-full object-contain bg-slate-900 border-2 border-white shadow-sm p-0.5">
                                        @endif
                                    </div>
                                    <span>{{ $inscripcion->presentacion?->nombre_presentacion ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $inscripcion->categoria?->categoria ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">
                                {{ $inscripcion->fase?->fase?->label() ?? '—' }}
                                @if ($inscripcion->departamento !== null)
                                    · {{ $inscripcion->departamento->nombre }}
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                <span class="text-slate-600">
                                    {{ $inscripcion->participantes->pluck('nombre')->implode(', ') }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">
                                <span @class(['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-slate-100 text-slate-700' => ($inscripcion->presentacion?->estado?->value ?? '') === App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value, 'bg-emerald-100 text-emerald-700' => ($inscripcion->presentacion?->estado?->value ?? '') === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value, 'bg-amber-100 text-amber-700' => ! in_array($inscripcion->presentacion?->estado?->value ?? '', [App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value, App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value], true)])>
                                    {{ $inscripcion->presentacion?->estado?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                @if ($inscripcion->puedeAdministrarse())
                                    <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="text-indigo-600 hover:text-indigo-800 ml-2">Editar</a>
                                    <form method="POST" action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" class="inline ml-2"
                                        onsubmit="return confirm('¿Eliminar esta inscripción y su presentación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                @else
                                    <span class="text-slate-300 ml-2">En uso</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Sin inscripciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $inscripciones->links() }}
        </div>
    </div>
</x-layouts.admin>