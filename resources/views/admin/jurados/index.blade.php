<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">Asignación de jurados</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.jurados.secreto') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-lg shadow">
                    🔒 Panel Jurado Secreto
                </a>
                <form method="POST" action="{{ route('admin.jurados.auto-asignar') }}" class="inline"
                      onsubmit="return confirm('¿Autoasignar jurados a todas las presentaciones pendientes?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg shadow">
                        Autoasignar pendientes
                    </button>
                </form>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <p class="mb-4 text-sm text-slate-500">
        La preselección requiere <strong>3 jurados</strong> por presentación y la etapa final <strong>5 jurados</strong> (incluyendo 1 jurado secreto).
    </p>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('admin.jurados.index') }}" class="mb-4 grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto] items-end">
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
            <div>
                <select name="fase" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todas las fases</option>
                    <option value="{{ App\Support\Enums\FaseEnum::PRESELECCION->value }}" @selected($faseSeleccionada === App\Support\Enums\FaseEnum::PRESELECCION->value)>
                        {{ App\Support\Enums\FaseEnum::PRESELECCION->label() }}
                    </option>
                    <option value="{{ App\Support\Enums\FaseEnum::FINAL->value }}" @selected($faseSeleccionada === App\Support\Enums\FaseEnum::FINAL->value)>
                        {{ App\Support\Enums\FaseEnum::FINAL->label() }}
                    </option>
                </select>
            </div>
            <div>
                <select name="departamento" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todos los deptos.</option>
                    @foreach ($departamentos as $departamento)
                        <option value="{{ $departamento->nombre }}" @selected($departamentoSeleccionado === $departamento->nombre)>
                            {{ $departamento->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <label class="inline-flex items-center text-sm text-slate-600 gap-2 mb-2">
                    <input type="checkbox" name="sin_completar" value="1" @checked($sinCompletar) class="rounded border-gray-300">
                    Solo incompletas
                </label>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg">
                    Filtrar
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Presentación</th>
                        <th class="py-2 pr-4">Categoría</th>
                        <th class="py-2 pr-4">Fase</th>
                        <th class="py-2 pr-4">Jurados asignados</th>
                        <th class="py-2">Editar asignación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($presentaciones as $presentacion)
                        @php
                            $requeridos = $presentacion->esPreseleccion() ? 3 : 5;
                            $asignados = $presentacion->jurados->pluck('id_usuario')->toArray();
                            $secretoAsignado = $presentacion->jurados->firstWhere('es_secreto', true)?->id_usuario;
                            $completa = count($asignados) === $requeridos;
                        @endphp
                        <tr class="border-b border-slate-100 align-top">
                            <td class="py-2 pr-4">
                                <p class="font-medium text-slate-800">{{ $presentacion->nombreMostrado() }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $presentacion->inscripcion?->participantes->pluck('nombre')->take(2)->implode(', ') }}
                                </p>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $presentacion->inscripcion?->categoria?->categoria ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">
                                {{ $presentacion->inscripcion?->fase?->fase?->label() ?? '—' }}
                            </td>
                            <td class="py-2 pr-4">
                                @forelse ($presentacion->jurados as $jurado)
                                    <span @class([
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold mb-1',
                                        'bg-purple-100 text-purple-800 border border-purple-200' => $jurado->es_secreto,
                                        'bg-sky-100 text-sky-700' => $jurado->es_controlador && ! $jurado->es_secreto,
                                        'bg-slate-100 text-slate-700' => ! $jurado->es_controlador && ! $jurado->es_secreto,
                                    ])>
                                        @if ($jurado->es_secreto)
                                            <span title="Jurado Secreto">🔒</span>
                                        @endif
                                        {{ $jurado->usuario?->nombre }} {{ $jurado->usuario?->paterno }}
                                        @if ($jurado->es_controlador)
                                            <span title="Controlador de cronómetro">⏱</span>
                                        @endif
                                    </span>
                                @empty
                                    <span class="text-slate-300">Sin asignación</span>
                                @endforelse
                                <div class="mt-1 text-xs">
                                    <span @class(['font-semibold', 'text-emerald-600' => $completa, 'text-amber-600' => ! $completa])>
                                        {{ count($asignados) }}/{{ $requeridos }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-2">
                                <details>
                                    <summary class="cursor-pointer text-indigo-600 hover:text-indigo-800 text-sm">Asignar…</summary>
                                    <form method="POST" action="{{ route('admin.jurados.asignar') }}" class="mt-2 grid gap-2 max-w-md bg-slate-50 p-3 rounded-xl border border-slate-200">
                                        @csrf
                                        <input type="hidden" name="presentacion_id" value="{{ $presentacion->id }}">
                                        <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-2 space-y-1 bg-white">
                                            @foreach ($jurados as $jurado)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox" name="usuario_ids[]" value="{{ $jurado->id }}"
                                                           @checked(in_array($jurado->id, $asignados, true))
                                                           class="rounded border-gray-300">
                                                    {{ $jurado->nombre }} {{ $jurado->paterno }}
                                                    @if ($jurado->puedeControlarTiempo())
                                                        <span class="text-xs text-sky-600">(cronómetro)</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>

                                        @if (! $presentacion->esPreseleccion())
                                            <div class="mt-1">
                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Designar Jurado Secreto (1 de los 5):</label>
                                                <select name="secreto_usuario_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                                                    <option value="">Automático / Según Categoría</option>
                                                    @foreach ($jurados as $jurado)
                                                        <option value="{{ $jurado->id }}" @selected($secretoAsignado === $jurado->id)>
                                                            {{ $jurado->nombre }} {{ $jurado->paterno }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg w-fit">
                                            Guardar ({{ $requeridos }} jurados)
                                        </button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Sin presentaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $presentaciones->links() }}
        </div>
    </div>
</x-layouts.admin>