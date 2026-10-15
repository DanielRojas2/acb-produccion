<x-layouts.admin>
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Auditoría y Trazabilidad de Calificaciones</h2>
                <p class="text-xs text-slate-500 mt-0.5">Historial oficial e inmutable de correcciones, motivos y snapshots de evaluación</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
                    🛡️ Registro Notarial & Auditoría
                </span>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">

        <!-- Banner Informativo -->
        <div class="bg-gradient-to-r from-[#5c0618] to-[#8b1528] text-white rounded-2xl p-6 shadow-md border border-amber-500/30">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold tracking-widest uppercase text-amber-300">XXXIII Festival Nacional de la Canción Boliviana</span>
                    <h3 class="text-lg font-black text-white">Libro Digital de Actas & Modificaciones</h3>
                    <p class="text-xs text-amber-100 max-w-2xl">
                        Cada ajuste de nota realizado por jurados o administradores genera un registro fotográfico (snapshot) inmutable del antes y después, con marca temporal y justificación obligatoria.
                    </p>
                </div>
                <div class="bg-black/30 backdrop-blur-sm rounded-xl p-3 px-5 border border-white/10 text-center shrink-0">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-300 block">Total Correcciones</span>
                    <span class="text-2xl font-black text-white tabular-nums">{{ $historiales->total() }}</span>
                </div>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
            <form method="GET" action="{{ route('admin.auditoria.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                
                <!-- Búsqueda texto -->
                <div class="lg:col-span-3">
                    <label for="buscar" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Buscar Participante / Motivo
                    </label>
                    <input type="text"
                           name="buscar"
                           id="buscar"
                           value="{{ request('buscar') }}"
                           placeholder="Ej: Dúo, Pérez, deliberación..."
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50">
                </div>

                <!-- Categoría -->
                <div class="lg:col-span-3">
                    <label for="categoria_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Categoría
                    </label>
                    <select name="categoria_id"
                            id="categoria_id"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>
                                {{ $cat->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Usuario / Jurado -->
                <div class="lg:col-span-2">
                    <label for="usuario_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Usuario / Jurado
                    </label>
                    <select name="usuario_id"
                            id="usuario_id"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50">
                        <option value="">Todos los usuarios</option>
                        @foreach ($usuarios as $usr)
                            <option value="{{ $usr->id }}" @selected(request('usuario_id') == $usr->id)>
                                {{ $usr->nombreCompleto() }} ({{ $usr->rol?->value }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div class="lg:col-span-2">
                    <label for="fecha_desde" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Desde
                    </label>
                    <input type="date"
                           name="fecha_desde"
                           id="fecha_desde"
                           value="{{ request('fecha_desde') }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-[#5c0618] focus:ring-[#5c0618] bg-slate-50">
                </div>

                <!-- Botones -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['buscar', 'categoria_id', 'usuario_id', 'fecha_desde', 'fecha_hasta']))
                        <a href="{{ route('admin.auditoria.index') }}"
                           class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition"
                           title="Limpiar filtros">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Registros de Auditoría -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Registros Oficiales ({{ $historiales->total() }})
                </span>
                <span class="text-xs text-slate-500">
                    Página {{ $historiales->currentPage() }} de {{ $historiales->lastPage() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Fecha / Hora</th>
                            <th class="py-3 px-4">Responsable</th>
                            <th class="py-3 px-4">Presentación / Participante</th>
                            <th class="py-3 px-4">Categoría & Depto</th>
                            <th class="py-3 px-4 text-center">Nota Anterior</th>
                            <th class="py-3 px-4 text-center">Nueva Nota</th>
                            <th class="py-3 px-4 text-center">Diferencia</th>
                            <th class="py-3 px-4">Motivo / Justificación</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($historiales as $item)
                            @php
                                $presentacion = $item->calificacion?->juradoPresentacion?->presentacion;
                                $inscripcion = $presentacion?->inscripcion;
                                $notaAnt = (float) ($item->datos_anteriores['nota_total'] ?? 0);
                                $notaNue = (float) ($item->datos_nuevos['nota_total'] ?? 0);
                                $diff = round($notaNue - $notaAnt, 2);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Fecha / Hora -->
                                <td class="py-3.5 px-4 font-mono text-slate-600 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">{{ $item->fecha->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $item->fecha->format('H:i:s') }}</span>
                                </td>

                                <!-- Responsable -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-full bg-slate-800 text-amber-300 font-black text-xs flex items-center justify-center shrink-0">
                                            {{ mb_substr($item->usuario?->nombre ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 leading-tight">
                                                {{ $item->usuario?->nombreCompleto() ?? 'Usuario desconocido' }}
                                            </p>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $item->usuario?->rol?->value ?? 'ROL' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Presentación -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 leading-snug">
                                        {{ $presentacion ? $presentacion->nombreMostrado() : "Calificación #{$item->id_calificacion}" }}
                                    </div>
                                    <span class="text-[10px] text-slate-400">ID Pres: #{{ $presentacion?->id ?? '-' }}</span>
                                </td>

                                <!-- Categoría & Depto -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">
                                        {{ $inscripcion?->categoria?->categoria ?? 'Sin categoría' }}
                                    </span>
                                    <span class="text-[10px] text-slate-500">
                                        {{ $inscripcion?->departamento?->nombre ?? 'Nacional' }}
                                    </span>
                                </td>

                                <!-- Nota Anterior -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-500 tabular-nums">
                                    {{ number_format($notaAnt, 2) }}
                                </td>

                                <!-- Nueva Nota -->
                                <td class="py-3.5 px-4 text-center font-mono font-black text-[#5c0618] tabular-nums">
                                    {{ number_format($notaNue, 2) }}
                                </td>

                                <!-- Diferencia -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap font-mono font-bold tabular-nums">
                                    @if ($diff > 0)
                                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px]">
                                            +{{ number_format($diff, 2) }}
                                        </span>
                                    @elseif ($diff < 0)
                                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200 text-[11px]">
                                            {{ number_format($diff, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[11px]">
                                            0.00
                                        </span>
                                    @endif
                                </td>

                                <!-- Motivo -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="line-clamp-2 text-slate-700 italic bg-amber-50/50 p-1.5 rounded-lg border border-amber-100 text-[11px]">
                                        «{{ $item->motivo ?? 'Sin motivo especificado' }}»
                                    </div>
                                </td>

                                <!-- Acción -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.auditoria.show', $item) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#5c0618] hover:text-white text-slate-700 font-bold text-xs transition">
                                        <span>🔍</span>
                                        <span>Ver Detalle</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">📜</div>
                                    <p class="font-bold text-slate-600 text-sm">No se encontraron registros de auditoría</p>
                                    <p class="text-xs text-slate-400 mt-1">Todas las calificaciones se mantienen en su estado original sin correcciones.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($historiales->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $historiales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
