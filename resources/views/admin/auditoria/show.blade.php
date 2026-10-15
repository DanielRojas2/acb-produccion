<x-layouts.admin>
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.auditoria.index') }}"
                   class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-1 transition">
                    ← Volver a Auditoría
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Detalle de Auditoría #{{ $historial->id }}</h2>
                    <p class="text-xs text-slate-500">Comparativa técnica de modificación de calificación</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
                    ID Calificación #{{ $historial->id_calificacion }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Fila de Tarjetas Informativas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Tarjeta 1: Participante & Presentación -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Participante / Obra</span>
                <div>
                    <h3 class="text-base font-black text-slate-900 leading-tight">
                        {{ $presentacion ? $presentacion->nombreMostrado() : 'Presentación eliminada' }}
                    </h3>
                    <p class="text-xs text-[#8b1528] font-bold mt-0.5">
                        {{ $categoria?->categoria ?? 'Sin Categoría' }}
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Depto: <span class="font-semibold text-slate-700">{{ $presentacion?->inscripcion?->departamento?->nombre ?? 'Nacional' }}</span>
                    </p>
                </div>
            </div>

            <!-- Tarjeta 2: Usuario que aplicó la corrección -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Responsable de Modificación</span>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-slate-900 text-amber-300 font-black text-sm flex items-center justify-center shrink-0">
                        {{ mb_substr($historial->usuario?->nombre ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">
                            {{ $historial->usuario?->nombreCompleto() ?? 'Usuario desconocido' }}
                        </p>
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                            Rol: {{ $historial->usuario?->rol?->value ?? 'N/A' }}
                        </span>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            Fecha: {{ $historial->fecha->format('d/m/Y H:i:s') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Impacto en Nota Total -->
            <div class="bg-[#3d040f] text-white rounded-2xl p-5 shadow-sm border border-amber-500/30 flex flex-col justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300 block">Variación Global de Nota</span>
                <div class="flex items-center justify-between gap-2 mt-2">
                    <div class="text-center">
                        <span class="text-[10px] text-slate-300 block">Antes</span>
                        <span class="text-xl font-bold text-slate-200 tabular-nums">{{ number_format($notaTotalAnterior, 2) }}</span>
                    </div>
                    <div class="text-amber-400 text-lg font-black">→</div>
                    <div class="text-center">
                        <span class="text-[10px] text-amber-300 block">Después</span>
                        <span class="text-2xl font-black text-white tabular-nums">{{ number_format($notaTotalNueva, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-300 block">Diferencia</span>
                        @if ($diferenciaTotal > 0)
                            <span class="text-sm font-black text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/40">
                                +{{ number_format($diferenciaTotal, 2) }}
                            </span>
                        @elseif ($diferenciaTotal < 0)
                            <span class="text-sm font-black text-rose-400 bg-rose-950/60 px-2 py-0.5 rounded-md border border-rose-500/40">
                                {{ number_format($diferenciaTotal, 2) }}
                            </span>
                        @else
                            <span class="text-sm font-bold text-slate-300 bg-black/40 px-2 py-0.5 rounded-md">
                                0.00
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Motivo Oficial Justificado -->
        <div class="bg-amber-50/80 border-2 border-amber-300/80 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-amber-700 font-bold text-xs uppercase tracking-wider">⚖️ Justificación Oficial del Cambio</span>
                <span class="text-[10px] bg-amber-200 text-amber-900 font-bold px-2 py-0.5 rounded-full">Campo Obligatorio Registrado</span>
            </div>
            <p class="text-slate-900 text-sm font-medium italic bg-white/70 p-3 rounded-xl border border-amber-200">
                «{{ $historial->motivo ?? 'Sin motivo especificado' }}»
            </p>
        </div>

        <!-- Tabla Comparativa Detallada de Parámetros -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-black text-slate-900 text-sm">
                    Desglose de Parámetros de Evaluación (Snapshot Anterior vs. Nuevo)
                </h3>
                <span class="text-xs text-slate-500">
                    Escala sobre 100 Puntos
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Parámetro</th>
                            <th class="py-3 px-4 text-center">Puntaje Máximo</th>
                            <th class="py-3 px-4 text-center bg-slate-200/40">Puntaje Anterior</th>
                            <th class="py-3 px-4 text-center bg-amber-100/40">Puntaje Nuevo</th>
                            <th class="py-3 px-4 text-center">Variación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($comparativaParametros as $p)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $p['nombre'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-500 tabular-nums">
                                    {{ number_format($p['valor_maximo'], 2) }} pts
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-600 bg-slate-50/50 tabular-nums">
                                    {{ number_format($p['valor_anterior'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-black text-[#5c0618] bg-amber-50/30 tabular-nums">
                                    {{ number_format($p['valor_nuevo'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold tabular-nums">
                                    @if ($p['diferencia'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px]">
                                            +{{ number_format($p['diferencia'], 2) }}
                                        </span>
                                    @elseif ($p['diferencia'] < 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[11px]">
                                            {{ number_format($p['diferencia'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">=</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td class="py-3 px-4 uppercase text-slate-700">Total General</td>
                            <td class="py-3 px-4 text-center text-slate-700">100.00 pts</td>
                            <td class="py-3 px-4 text-center font-mono text-slate-700 tabular-nums">{{ number_format($notaTotalAnterior, 2) }}</td>
                            <td class="py-3 px-4 text-center font-mono text-[#5c0618] text-sm tabular-nums">{{ number_format($notaTotalNueva, 2) }}</td>
                            <td class="py-3 px-4 text-center font-mono tabular-nums">
                                @if ($diferenciaTotal > 0)
                                    <span class="text-emerald-700 font-black">+{{ number_format($diferenciaTotal, 2) }}</span>
                                @elseif ($diferenciaTotal < 0)
                                    <span class="text-rose-700 font-black">{{ number_format($diferenciaTotal, 2) }}</span>
                                @else
                                    <span class="text-slate-500">0.00</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Metadatos de Snapshot JSON (Para Inspección Forense) -->
        <details class="bg-slate-50 rounded-2xl border border-slate-200 p-4 text-xs">
            <summary class="font-bold text-slate-700 cursor-pointer hover:text-slate-900 select-none">
                ⚙️ Ver volcado forense crudo (Payload JSON de Auditoría)
            </summary>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div>
                    <span class="font-bold text-slate-600 block mb-1">datos_anteriores (JSON)</span>
                    <pre class="bg-slate-900 text-amber-300 p-3 rounded-xl overflow-x-auto font-mono text-[11px]">{{ json_encode($historial->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div>
                    <span class="font-bold text-slate-600 block mb-1">datos_nuevos (JSON)</span>
                    <pre class="bg-slate-900 text-emerald-300 p-3 rounded-xl overflow-x-auto font-mono text-[11px]">{{ json_encode($historial->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </details>
    </div>
</x-layouts.admin>
