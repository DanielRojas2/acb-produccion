<x-layouts.admin>
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Centro de Reportes y Actas Oficiales</h2>
                <p class="text-xs text-slate-500 mt-0.5">Emisión de resultados consolidados, actas de escrutinio para Notaría y sábanas de evaluación</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
                    🏛️ Fase 7 — Reportes & Auditoría
                </span>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">

        <!-- Selector de Filtros Principales -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
            <form method="GET" action="{{ route('admin.reportes.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                
                <!-- Fase -->
                <div class="lg:col-span-3">
                    <label for="fase_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Fase del Festival
                    </label>
                    <select name="fase_id" id="fase_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50 font-bold" onchange="this.form.submit()">
                        @foreach ($fases as $f)
                            <option value="{{ $f->id }}" @selected($faseSeleccionada?->id == $f->id)>
                                {{ $f->fase instanceof \App\Support\Enums\FaseEnum ? $f->fase->label() : $f->fase }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Categoría -->
                <div class="lg:col-span-4">
                    <label for="categoria_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Categoría Oficial
                    </label>
                    <select name="categoria_id" id="categoria_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50 font-bold" onchange="this.form.submit()">
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected($categoriaSeleccionada?->id == $cat->id)>
                                {{ $cat->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Departamento (Opcional en preselección) -->
                <div class="lg:col-span-3">
                    <label for="departamento_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Departamento
                    </label>
                    <select name="departamento_id" id="departamento_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50 font-semibold" onchange="this.form.submit()">
                        <option value="">Todos los departamentos</option>
                        @foreach ($departamentos as $dep)
                            <option value="{{ $dep->id }}" @selected($departamentoId == $dep->id)>
                                {{ $dep->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botón de aplicar -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>

        @if ($reporte && $categoriaSeleccionada && $faseSeleccionada)
            <!-- Tarjetas de Salidas Oficiales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Tarjeta 1: Acta Oficial Notarial -->
                <div class="bg-gradient-to-br from-[#5c0618] to-[#8b1528] text-white rounded-3xl p-6 shadow-md border border-amber-500/30 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest uppercase text-amber-300">Documento Legal</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px]">Print-Ready</span>
                        </div>
                        <h3 class="text-lg font-black text-white leading-snug">Acta Notarial de Escrutinio</h3>
                        <p class="text-xs text-amber-100">
                            Formato oficial membretado para Notario de Fe Pública, con firmas de jurados y comité organizador.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('admin.reportes.acta', ['fase_id' => $faseSeleccionada->id, 'categoria_id' => $categoriaSeleccionada->id, 'departamento_id' => $departamentoId]) }}"
                           target="_blank"
                           class="w-full py-3 px-4 rounded-xl bg-white hover:bg-amber-100 text-[#5c0618] font-black text-xs text-center inline-flex items-center justify-center gap-2 shadow-lg transition transform hover:-translate-y-0.5">
                            <span>🖨️</span>
                            <span>Abrir e Imprimir Acta Notarial</span>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta 2: Sábana de Calificación por Parámetros -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400">Matriz Técnica</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px]">Parámetros</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 leading-snug">Sábana Completa de Evaluación</h3>
                        <p class="text-xs text-slate-500">
                            Matriz cruzada con la puntuación otorgada por cada jurado a cada uno de los parámetros de la categoría.
                        </p>
                    </div>
                    <div class="pt-2 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('admin.reportes.sabana', ['fase_id' => $faseSeleccionada->id, 'categoria_id' => $categoriaSeleccionada->id, 'departamento_id' => $departamentoId]) }}"
                           class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs text-center inline-flex items-center justify-center gap-1.5 transition">
                            <span>📐</span>
                            <span>Ver Sábana</span>
                        </a>
                        <a href="{{ route('admin.reportes.exportar.sabana', ['fase_id' => $faseSeleccionada->id, 'categoria_id' => $categoriaSeleccionada->id, 'departamento_id' => $departamentoId]) }}"
                           class="py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center justify-center gap-1 transition shadow"
                           title="Descargar CSV Sábana">
                            <span>📥 CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta 3: Exportación y Estadísticas -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400">Exportación Rápida</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">Tabular</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 leading-snug">Descarga de Resultados</h3>
                        <div class="space-y-1 text-xs text-slate-500">
                            <div class="flex justify-between">
                                <span>Total Participantes:</span>
                                <span class="font-bold text-slate-800 tabular-nums">{{ $reporte['total_participantes'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Promedio de Categoría:</span>
                                <span class="font-bold text-[#8b1528] tabular-nums">{{ number_format($reporte['promedio_general'], 2) }} pts</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('admin.reportes.exportar.resultados', ['fase_id' => $faseSeleccionada->id, 'categoria_id' => $categoriaSeleccionada->id, 'departamento_id' => $departamentoId]) }}"
                           class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center inline-flex items-center justify-center gap-2 shadow transition">
                            <span>📥</span>
                            <span>Descargar CSV Resultados Oficiales</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Previsualización de Tabla de Resultados -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900">
                            Resultados Consolidados: {{ $categoriaSeleccionada->categoria }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            Fase: <strong class="text-slate-800">{{ $faseSeleccionada->fase }}</strong> |
                            Departamento: <strong class="text-slate-800">{{ $reporte['departamento']?->nombre ?? 'Todos' }}</strong>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.reportes.resultados', ['fase_id' => $faseSeleccionada->id, 'categoria_id' => $categoriaSeleccionada->id, 'departamento_id' => $departamentoId]) }}"
                           class="px-3.5 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs inline-flex items-center gap-1.5 transition">
                            <span>🔍</span>
                            <span>Vista Completa de Resultados</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 text-center">Puesto</th>
                                <th class="py-3 px-4">Participante / Conjunto</th>
                                <th class="py-3 px-4">Departamento</th>
                                @foreach ($reporte['jurados_cabecera'] as $jurNom)
                                    <th class="py-3 px-4 text-center">{{ $jurNom }}</th>
                                @endforeach
                                <th class="py-3 px-4 text-center bg-amber-50/50">Nota Final</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reporte['items'] as $item)
                                <tr @class([
                                    'hover:bg-slate-50 transition',
                                    'bg-amber-50/20 font-semibold' => $item['posicion'] === 1,
                                ])>
                                    <!-- Puesto -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if ($item['posicion'] === 1)
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-amber-400 text-amber-950 font-black text-xs shadow-sm">
                                                1°
                                            </span>
                                        @elseif ($item['posicion'] === 2 && ! $categoriaSeleccionada->tieneUnSoloGanador())
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-slate-300 text-slate-800 font-black text-xs">
                                                2°
                                            </span>
                                        @elseif ($item['posicion'] === 3 && ! $categoriaSeleccionada->tieneUnSoloGanador())
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-amber-700 text-amber-100 font-black text-xs">
                                                3°
                                            </span>
                                        @elseif ($item['posicion'] !== null)
                                            <span class="text-slate-500 font-bold tabular-nums">
                                                {{ $item['posicion'] }}°
                                            </span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>

                                    <!-- Participante -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 leading-snug">
                                            {{ $item['nombre_presentacion'] }}
                                        </div>
                                        @if (!empty($item['integrantes']) && count($item['integrantes']) > 1)
                                            <p class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5">
                                                {{ implode(', ', $item['integrantes']) }}
                                            </p>
                                        @endif
                                    </td>

                                    <!-- Depto -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="text-slate-700 font-semibold">{{ $item['departamento'] }}</span>
                                    </td>

                                    <!-- Votos de Jurados -->
                                    @foreach ($reporte['jurados_cabecera'] as $jurNom)
                                        @php
                                            $v = $item['votos_jurados'][$jurNom] ?? null;
                                        @endphp
                                        <td class="py-3.5 px-4 text-center font-mono tabular-nums whitespace-nowrap">
                                            @if ($v && $v['nota_total'] !== null)
                                                <span class="font-bold text-slate-800">{{ number_format($v['nota_total'], 2) }}</span>
                                                @if ($v['es_secreto'])
                                                    <span class="text-[9px] text-amber-600 block">🔒 Secreto</span>
                                                @endif
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <!-- Nota Final -->
                                    <td class="py-3.5 px-4 text-center font-mono font-black text-[#5c0618] bg-amber-50/40 text-sm tabular-nums whitespace-nowrap">
                                        @if ($item['nota_final'] !== null)
                                            {{ number_format($item['nota_final'], 2) }}
                                        @else
                                            <span class="text-slate-300 font-normal text-xs">Sin nota</span>
                                        @endif
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $item['estado_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 5 + count($reporte['jurados_cabecera']) }}" class="py-10 text-center text-slate-400">
                                        No hay presentaciones registradas para esta categoría y fase.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts.admin>
