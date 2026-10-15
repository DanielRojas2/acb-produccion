<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-800">Presentaciones</h2>
            <a href="{{ route('admin.presentaciones.escenario') }}" target="_blank"
               class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow">
                Ver escenario
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

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('admin.presentaciones.index') }}" class="mb-4 grid gap-3 md:grid-cols-[1fr_1fr_1fr_1fr_auto] items-end">
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
            <div>
                <select name="estado" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->value }}" @selected($estadoSeleccionado === $estado->value)>
                            {{ $estado->label() }}
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
                        <th class="py-2 pr-4">Estado</th>
                        <th class="py-2 pr-4">Cronómetro</th>
                        <th class="py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($presentaciones as $presentacion)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4">
                                <div class="flex items-center gap-3">
                                    @if ($presentacion->fotoUrl())
                                        <img src="{{ $presentacion->fotoUrl() }}" alt="" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0 shadow-sm">
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $presentacion->nombreMostrado() }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ $presentacion->inscripcion?->participantes->pluck('nombre')->take(3)->implode(', ') }}
                                            @if (($presentacion->inscripcion?->participantes->count() ?? 0) > 3)
                                                +{{ $presentacion->inscripcion->participantes->count() - 3 }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $presentacion->inscripcion?->categoria?->categoria ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">
                                {{ $presentacion->inscripcion?->fase?->fase?->label() ?? '—' }}
                                @if ($presentacion->inscripcion?->departamento !== null)
                                    · {{ $presentacion->inscripcion->departamento->nombre }}
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                <span @class([
                                    'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                    'bg-slate-100 text-slate-700' => $presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value,
                                    'bg-amber-100 text-amber-700' => $presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value,
                                    'bg-indigo-100 text-indigo-700' => $presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO->value,
                                    'bg-emerald-100 text-emerald-700' => $presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value,
                                ])>
                                    {{ $presentacion->estado?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">
                                <span class="font-mono text-slate-600 tabular-nums">
                                    {{ gmdate('i:s', $presentacion->segundosRestantes()) }}
                                </span>
                                <span class="text-xs text-slate-400">/ {{ $presentacion->tiempo_maximo }}:00</span>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                @if ($presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value)
                                    <form method="POST" action="{{ route('admin.presentaciones.transicion', $presentacion) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="estado" value="{{ App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value }}">
                                        <button type="submit" class="text-amber-600 hover:text-amber-800 ml-2">Al escenario</button>
                                    </form>
                                @endif

                                @if ($presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value)
                                    <form method="POST" action="{{ route('admin.presentaciones.transicion', $presentacion) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="estado" value="{{ App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO->value }}">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 ml-2">Iniciar</button>
                                    </form>
                                @endif

                                @if (Illuminate\Support\Facades\Gate::allows('controlarCronometro', $presentacion) && $presentacion->estado?->value !== App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value && ! $presentacion->cronometroCorriendo() && $presentacion->cronometro_iniciado_at === null)
                                    <form method="POST" action="{{ route('presentaciones.cronometro.iniciar', $presentacion) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 ml-2">Iniciar cronómetro</button>
                                    </form>
                                @endif

                                @if (Illuminate\Support\Facades\Gate::allows('controlarCronometro', $presentacion) && $presentacion->cronometroCorriendo())
                                    <form method="POST" action="{{ route('presentaciones.cronometro.detener', $presentacion) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 ml-2">Detener cronómetro</button>
                                    </form>
                                @endif

                                @if ($presentacion->estado?->value === App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO->value)
                                    <form method="POST" action="{{ route('admin.presentaciones.transicion', $presentacion) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="estado" value="{{ App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value }}">
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 ml-2">Finalizar</button>
                                    </form>
                                @endif

                                <a href="{{ route('admin.calificaciones.presentacion', $presentacion) }}"
                                   class="text-slate-600 hover:text-slate-900 ml-2 font-medium"
                                   title="Ver evaluaciones de jurados">
                                    Notas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Sin presentaciones.</td>
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