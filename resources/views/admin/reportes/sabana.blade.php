<x-layouts.admin>
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reportes.index', ['fase_id' => $sabana['fase']->id, 'categoria_id' => $sabana['categoria']->id]) }}"
                   class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center gap-1 transition">
                    ← Volver a Reportes
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Sábana de Calificación por Parámetros</h2>
                    <p class="text-xs text-slate-500">Categoría: {{ $sabana['categoria']->categoria }} | Fase: {{ $sabana['fase']->fase instanceof \App\Support\Enums\FaseEnum ? $sabana['fase']->fase->label() : $sabana['fase']->fase }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reportes.exportar.sabana', ['fase_id' => $sabana['fase']->id, 'categoria_id' => $sabana['categoria']->id, 'departamento_id' => $sabana['departamento']?->id]) }}"
                   class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow transition">
                    <span>📥</span>
                    <span>Descargar Sábana en CSV</span>
                </a>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">

        <!-- Banner de Parámetros de la Categoría -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider text-[#5c0618]">
                Parámetros Evaluados en {{ $sabana['categoria']->categoria }} (Total: 100 Puntos)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ($sabana['parametros'] as $param)
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Parámetro #{{ $param->orden }}</span>
                        <p class="font-bold text-slate-800 text-xs leading-snug">{{ $param->parametro?->parametro }}</p>
                        <span class="inline-block text-[11px] font-black text-[#8b1528] bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                            Máx: {{ number_format($param->valor_maximo, 0) }} pts
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Matriz Sábana de Calificaciones -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="font-black text-slate-900 text-base">Matriz Técnica de Evaluación Cruzada</h3>
                <span class="text-xs text-slate-500">{{ count($sabana['filas']) }} Participaciones Registradas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Participante / Departamento</th>
                            <th class="py-3 px-4">Jurado Evaluador</th>
                            @foreach ($sabana['parametros'] as $p)
                                <th class="py-3 px-3 text-center">
                                    <span class="block truncate max-w-[120px]" title="{{ $p->parametro?->parametro }}">{{ $p->parametro?->parametro }}</span>
                                    <span class="text-[9px] text-slate-400 block font-normal">(Máx {{ number_format($p->valor_maximo, 0) }})</span>
                                </th>
                            @endforeach
                            <th class="py-3 px-4 text-center bg-slate-200/50">Total Jurado</th>
                            <th class="py-3 px-4 text-center bg-amber-100/40">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($sabana['filas'] as $fila)
                            @php
                                $totalJurados = count($fila['jurados']);
                            @endphp

                            @if ($totalJurados === 0)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        {{ $fila['nombre'] }}
                                        <span class="text-[10px] text-slate-400 block font-normal">{{ $fila['departamento'] }}</span>
                                    </td>
                                    <td colspan="{{ count($sabana['parametros']) + 2 }}" class="py-3.5 px-4 text-center text-slate-400 italic">
                                        Sin jurados asignados
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-400">
                                        {{ $fila['nota_final'] !== null ? number_format($fila['nota_final'], 2) : '-' }}
                                    </td>
                                </tr>
                            @else
                                @foreach ($fila['jurados'] as $idx => $jur)
                                    <tr class="hover:bg-slate-50 transition">
                                        @if ($idx === 0)
                                            <td rowspan="{{ $totalJurados }}" class="py-3.5 px-4 font-bold text-slate-900 border-r border-slate-200 align-top bg-white">
                                                <div class="sticky top-0">
                                                    <span class="block text-sm font-black">{{ $fila['nombre'] }}</span>
                                                    <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">{{ $fila['departamento'] }}</span>
                                                    <span class="text-[10px] text-slate-400 block">ID: #{{ $fila['presentacion']->id }}</span>
                                                </div>
                                            </td>
                                        @endif

                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="font-bold text-slate-800">{{ $jur['jurado_nombre'] }}</span>
                                            @if ($jur['es_secreto'])
                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200 font-bold ml-1">🔒 Secreto</span>
                                            @endif
                                        </td>

                                        @foreach ($sabana['parametros'] as $param)
                                            @php
                                                $val = $jur['parametros_valores'][$param->id] ?? null;
                                            @endphp
                                            <td class="py-3 px-3 text-center font-mono tabular-nums">
                                                @if ($val !== null)
                                                    <span class="font-bold text-slate-800">{{ number_format($val, 2) }}</span>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="py-3 px-4 text-center font-mono font-black text-slate-900 bg-slate-100/60 tabular-nums">
                                            {{ $jur['nota_total'] !== null ? number_format($jur['nota_total'], 2) : '-' }}
                                        </td>

                                        @if ($idx === 0)
                                            <td rowspan="{{ $totalJurados }}" class="py-3.5 px-4 text-center font-mono font-black text-[#5c0618] bg-amber-50/50 text-sm border-l border-slate-200 align-middle tabular-nums">
                                                {{ $fila['nota_final'] !== null ? number_format($fila['nota_final'], 2) : 'S/C' }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ 4 + count($sabana['parametros']) }}" class="py-10 text-center text-slate-400">
                                    No hay participaciones para mostrar en esta categoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
