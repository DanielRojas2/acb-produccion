<x-layouts.admin>
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reportes.index', ['fase_id' => $reporte['fase']->id, 'categoria_id' => $reporte['categoria']->id]) }}"
                   class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-1 transition">
                    ← Volver a Reportes
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Resultados Oficiales: {{ $reporte['categoria']->categoria }}</h2>
                    <p class="text-xs text-slate-500">Fase: {{ $reporte['fase']->fase instanceof \App\Support\Enums\FaseEnum ? $reporte['fase']->fase->label() : $reporte['fase']->fase }} | Ámbito: {{ $reporte['departamento']?->nombre ?? 'Nacional' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reportes.acta', ['fase_id' => $reporte['fase']->id, 'categoria_id' => $reporte['categoria']->id, 'departamento_id' => $reporte['departamento']?->id]) }}"
                   target="_blank"
                   class="px-4 py-2 rounded-xl bg-[#5c0618] hover:bg-[#3d040f] text-white font-bold text-xs inline-flex items-center gap-1.5 shadow transition">
                    <span>📜</span>
                    <span>Acta Notarial</span>
                </a>
                <a href="{{ route('admin.reportes.exportar.resultados', ['fase_id' => $reporte['fase']->id, 'categoria_id' => $reporte['categoria']->id, 'departamento_id' => $reporte['departamento']?->id]) }}"
                   class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow transition">
                    <span>📥</span>
                    <span>Descargar CSV</span>
                </a>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">

        <!-- Podio de Ganadores (Top 3) -->
        @php
            $primeros = collect($reporte['items'])->where('posicion', '<=', 3)->whereNotNull('posicion')->values();
        @endphp

        @if ($primeros->isNotEmpty())
            @if ($reporte['categoria']->tieneUnSoloGanador())
                <!-- Ganador Único (Ñusta / Especial) -->
                @php $primero = $primeros->firstWhere('posicion', 1); @endphp
                @if ($primero)
                    <div class="max-w-lg mx-auto pt-4">
                        <div class="bg-gradient-to-b from-[#3d040f] to-[#5c0618] text-white rounded-3xl p-6 sm:p-8 shadow-xl border-2 border-amber-400 text-center space-y-4 relative overflow-hidden">
                            <div class="h-2 w-full bg-amber-400 absolute top-0 left-0"></div>
                            <div class="h-16 w-16 rounded-full bg-amber-400 text-[#3d040f] font-black text-2xl flex items-center justify-center mx-auto shadow-lg ring-4 ring-amber-300/30">
                                👑 1°
                            </div>
                            <div>
                                <span class="text-xs font-black uppercase tracking-widest text-amber-300 block">★ {{ $reporte['categoria']->etiquetaGanador() }} ★</span>
                                <h3 class="font-black text-white text-lg sm:text-xl leading-tight mt-1">{{ $primero['nombre_presentacion'] }}</h3>
                                <p class="text-xs text-amber-200 mt-0.5">{{ $primero['departamento'] }}</p>
                            </div>
                            <div class="bg-black/30 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                                <span class="text-[10px] uppercase font-bold text-amber-300 block">Puntaje de Excelencia</span>
                                <span class="text-3xl sm:text-4xl font-black text-amber-300 tabular-nums">{{ number_format($primero['nota_final'], 2) }}</span>
                                <span class="text-xs text-amber-100/70">/ 100 pts</span>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end pt-4">
                    
                    <!-- 2do Lugar (Izquierda) -->
                    @php $segundo = $primeros->firstWhere('posicion', 2); @endphp
                    @if ($segundo)
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 text-center space-y-3 relative overflow-hidden order-2 md:order-1">
                            <div class="h-2 w-full bg-slate-300 absolute top-0 left-0"></div>
                            <div class="h-12 w-12 rounded-full bg-slate-200 text-slate-700 font-black text-lg flex items-center justify-center mx-auto shadow-inner">
                                2°
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Segundo Lugar</span>
                                <h3 class="font-black text-slate-900 text-base leading-tight mt-1">{{ $segundo['nombre_presentacion'] }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $segundo['departamento'] }}</p>
                            </div>
                            <div class="bg-slate-100 rounded-2xl p-3 border border-slate-200">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Puntaje Final</span>
                                <span class="text-2xl font-black text-slate-800 tabular-nums">{{ number_format($segundo['nota_final'], 2) }}</span>
                                <span class="text-[10px] text-slate-400">/ 100</span>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:block order-1"></div>
                    @endif

                    <!-- 1er Lugar (Centro, Destacado) -->
                    @php $primero = $primeros->firstWhere('posicion', 1); @endphp
                    @if ($primero)
                        <div class="bg-gradient-to-b from-[#3d040f] to-[#5c0618] text-white rounded-3xl p-6 sm:p-8 shadow-xl border-2 border-amber-400 text-center space-y-4 relative overflow-hidden order-1 md:order-2 transform md:-translate-y-2">
                            <div class="h-2 w-full bg-amber-400 absolute top-0 left-0"></div>
                            <div class="h-16 w-16 rounded-full bg-amber-400 text-[#3d040f] font-black text-2xl flex items-center justify-center mx-auto shadow-lg ring-4 ring-amber-300/30">
                                👑 1°
                            </div>
                            <div>
                                <span class="text-xs font-black uppercase tracking-widest text-amber-300 block">★ Ganador Oficial ★</span>
                                <h3 class="font-black text-white text-lg sm:text-xl leading-tight mt-1">{{ $primero['nombre_presentacion'] }}</h3>
                                <p class="text-xs text-amber-200 mt-0.5">{{ $primero['departamento'] }}</p>
                            </div>
                            <div class="bg-black/30 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                                <span class="text-[10px] uppercase font-bold text-amber-300 block">Puntaje de Excelencia</span>
                                <span class="text-3xl sm:text-4xl font-black text-amber-300 tabular-nums">{{ number_format($primero['nota_final'], 2) }}</span>
                                <span class="text-xs text-amber-100/70">/ 100 pts</span>
                            </div>
                        </div>
                    @endif

                    <!-- 3er Lugar (Derecha) -->
                    @php $tercero = $primeros->firstWhere('posicion', 3); @endphp
                    @if ($tercero)
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 text-center space-y-3 relative overflow-hidden order-3">
                            <div class="h-2 w-full bg-amber-700 absolute top-0 left-0"></div>
                            <div class="h-12 w-12 rounded-full bg-amber-100 text-amber-900 font-black text-lg flex items-center justify-center mx-auto shadow-inner">
                                3°
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Tercer Lugar</span>
                                <h3 class="font-black text-slate-900 text-base leading-tight mt-1">{{ $tercero['nombre_presentacion'] }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $tercero['departamento'] }}</p>
                            </div>
                            <div class="bg-slate-100 rounded-2xl p-3 border border-slate-200">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Puntaje Final</span>
                                <span class="text-2xl font-black text-slate-800 tabular-nums">{{ number_format($tercero['nota_final'], 2) }}</span>
                                <span class="text-[10px] text-slate-400">/ 100</span>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:block order-3"></div>
                    @endif
                </div>
            @endif
        @endif

        <!-- Tabla Completa de Posiciones -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Tabla Oficial Consolidada</h3>
                    <p class="text-xs text-slate-500">{{ $reporte['total_participantes'] }} participantes en competencia</p>
                </div>
                <div class="text-xs font-bold text-slate-700">
                    Promedio General: <span class="text-[#5c0618] font-black text-sm">{{ number_format($reporte['promedio_general'], 2) }}</span> pts
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
                                'bg-amber-50/30' => $item['posicion'] === 1,
                            ])>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if ($item['posicion'] === 1)
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-amber-400 text-amber-950 font-black text-xs shadow-sm">
                                            1°
                                        </span>
                                    @elseif ($item['posicion'] === 2 && ! $reporte['categoria']->tieneUnSoloGanador())
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-slate-300 text-slate-800 font-black text-xs">
                                            2°
                                        </span>
                                    @elseif ($item['posicion'] === 3 && ! $reporte['categoria']->tieneUnSoloGanador())
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-amber-700 text-amber-100 font-black text-xs">
                                            3°
                                        </span>
                                    @elseif ($item['posicion'] !== null)
                                        <span class="font-bold text-slate-600 tabular-nums">{{ $item['posicion'] }}°</span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $item['nombre_presentacion'] }}</span>
                                    @if (!empty($item['integrantes']) && count($item['integrantes']) > 1)
                                        <span class="text-[10px] text-slate-400 block">{{ implode(', ', $item['integrantes']) }}</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    {{ $item['departamento'] }}
                                </td>

                                @foreach ($reporte['jurados_cabecera'] as $jurNom)
                                    @php $v = $item['votos_jurados'][$jurNom] ?? null; @endphp
                                    <td class="py-3.5 px-4 text-center font-mono tabular-nums whitespace-nowrap">
                                        @if ($v && $v['nota_total'] !== null)
                                            <span class="font-bold text-slate-800">{{ number_format($v['nota_total'], 2) }}</span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="py-3.5 px-4 text-center font-mono font-black text-[#5c0618] bg-amber-50/50 text-sm tabular-nums whitespace-nowrap">
                                    {{ $item['nota_final'] !== null ? number_format($item['nota_final'], 2) : 'S/C' }}
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item['estado_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($reporte['jurados_cabecera']) }}" class="py-10 text-center text-slate-400">
                                    No hay participaciones evaluadas en esta categoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
