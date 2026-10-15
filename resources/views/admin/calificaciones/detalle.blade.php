<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.presentaciones.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 mb-1 inline-flex items-center gap-1">
                    ← Volver a Presentaciones
                </a>
                <h2 class="text-xl font-bold text-slate-800">Detalle de Calificaciones</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full border border-amber-300">
                    {{ $presentacion->inscripcion?->fase?->fase?->label() ?? 'Etapa' }}
                </span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full border border-blue-300">
                    {{ $presentacion->inscripcion?->categoria?->categoria ?? 'Categoría' }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">
        <!-- Tarjeta Resumen de la Presentación -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-amber-400 font-semibold uppercase tracking-wider">Presentación</span>
                    <h1 class="text-2xl font-black mt-1">{{ $presentacion->nombreMostrado() }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-300">
                        <span><strong>Categoría:</strong> {{ $presentacion->inscripcion?->categoria?->categoria ?? '—' }}</span>
                        @if ($presentacion->inscripcion?->departamento)
                            <span>·</span>
                            <span><strong>Departamento:</strong> {{ $presentacion->inscripcion->departamento->nombre }}</span>
                        @endif
                        <span>·</span>
                        <span><strong>Estado:</strong> {{ $presentacion->estado?->label() }}</span>
                    </div>
                </div>

                <div class="bg-slate-800/90 border border-slate-700 rounded-xl p-5 text-right self-start md:self-auto min-w-[160px]">
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Nota Final Oficial</span>
                    <div class="text-3xl font-black text-amber-400 tabular-nums mt-1">
                        {{ $presentacion->nota_final !== null ? number_format($presentacion->nota_final, 2) : '—' }}
                    </div>
                    <span class="text-xs text-slate-400">Promedio de jurados</span>
                </div>
            </div>
        </div>

        <!-- Tabla de Calificaciones por Jurado -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800">Evaluaciones de la Mesa del Jurado</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Votos y notas registradas por parámetro para esta presentación</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3">Jurado</th>
                            <th class="px-5 py-3">Rol / Tipo</th>
                            <th class="px-5 py-3">Estado Evaluación</th>
                            <th class="px-5 py-3">Detalle de Parámetros</th>
                            <th class="px-5 py-3 text-right">Nota Total</th>
                            <th class="px-5 py-3 text-center">Auditoría</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($presentacion->jurados as $jurado)
                            @php
                                $calificacion = $jurado->calificacion;
                                $usuario = $jurado->usuario;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 font-semibold text-slate-900">
                                    {{ $usuario?->nombre }} {{ $usuario?->paterno }} {{ $usuario?->materno }}
                                    <span class="block text-xs font-normal text-slate-400">{{ $usuario?->email }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if ($jurado->es_secreto)
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-xs font-semibold rounded border border-purple-200">
                                                🔒 Jurado Secreto
                                            </span>
                                        @endif
                                        @if ($jurado->es_controlador)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-semibold rounded border border-blue-200">
                                                ⏱ Controlador
                                            </span>
                                        @endif
                                        @if (!$jurado->es_secreto && !$jurado->es_controlador)
                                            <span class="text-xs text-slate-500">Jurado regular</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($calificacion && $calificacion->esEmitida())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $calificacion->estado?->label() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded-full border border-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($calificacion && $calificacion->detalles->isNotEmpty())
                                        <div class="space-y-1">
                                            @foreach ($calificacion->detalles as $detalle)
                                                <div class="flex items-center justify-between text-xs gap-4">
                                                    <span class="text-slate-600 truncate max-w-[200px]" title="{{ $detalle->parametro?->parametro }}">
                                                        {{ $detalle->parametro?->parametro }}:
                                                    </span>
                                                    <span class="font-bold text-slate-800 tabular-nums">
                                                        {{ number_format($detalle->valor, 2) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sin notas registradas</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($calificacion && $calificacion->esEmitida())
                                        <span class="text-lg font-black text-slate-900 tabular-nums">
                                            {{ number_format($calificacion->nota_total, 2) }}
                                        </span>
                                        <span class="text-xs text-slate-400 block">/ 100 pts</span>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($calificacion && $calificacion->historiales->isNotEmpty())
                                        <a href="{{ route('admin.calificaciones.historial', $calificacion) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-200 transition">
                                            📋 Ver Historial ({{ $calificacion->historiales->count() }})
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Sin cambios</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-sm">
                                    No hay jurados asignados para esta presentación.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
