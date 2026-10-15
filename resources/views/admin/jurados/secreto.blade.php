<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Jurado Secreto y Cierre de Categorías</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gestión de jurados secretos, revelación y finalización de la Etapa Final</p>
            </div>
            <a href="{{ route('admin.jurados.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300">
                ← Asignación General de Jurados
            </a>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">
        <!-- Banner Informativo -->
        <div class="bg-gradient-to-r from-purple-900 to-indigo-900 text-white rounded-2xl p-6 shadow-md border border-purple-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/30 text-purple-200 border border-purple-400/40 mb-2">
                        🔒 Regla Oficial 2026 · Etapa Final (5 jurados)
                    </span>
                    <h1 class="text-2xl font-black">Control del Jurado Secreto</h1>
                    <p class="mt-1 text-sm text-purple-100 max-w-2xl leading-relaxed">
                        Cada categoría en la Etapa Final tiene 5 jurados, de los cuales <strong>exactamente 1 es secreto</strong>.
                        La nota del jurado secreto <strong>participa internamente en el promedio oficial</strong> desde que califica,
                        pero permanece oculta para el público hasta que el Administrador presiona <strong>Revelar</strong> o <strong>Finalizar Categoría</strong>.
                    </p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 flex items-center gap-2 shadow-sm">
                <span class="text-emerald-500 font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800 flex items-center gap-2 shadow-sm">
                <span class="text-red-500 font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Grid de Categorías -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($resumenCategorias as $item)
                @php
                    $categoria = $item['categoria'];
                    $secreto = $item['jurado_secreto'];
                    $estaRevelado = $item['jurado_secreto_revelado'];
                    $estaFinalizada = $item['finalizada'];
                @endphp
                <div @class([
                    'bg-white rounded-2xl border shadow-sm p-5 flex flex-col justify-between transition hover:shadow-md',
                    'border-emerald-300 ring-1 ring-emerald-300' => $estaFinalizada,
                    'border-purple-200' => $estaRevelado && ! $estaFinalizada,
                    'border-slate-200' => ! $estaRevelado && ! $estaFinalizada,
                ])>
                    <div>
                        <!-- Encabezado de la Tarjeta -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Categoría #{{ $item['id'] }}</span>
                                <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $item['nombre'] }}</h3>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                @if ($estaFinalizada)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-300">
                                        ✓ Finalizada
                                    </span>
                                @elseif ($estaRevelado)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-800 text-xs font-bold rounded-full border border-purple-300">
                                        🔓 Revelado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full border border-slate-300">
                                        🔒 Oculto
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Métricas y Participación -->
                        <div class="grid grid-cols-2 gap-2 my-3 p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-500 block">Presentaciones:</span>
                                <span class="font-bold text-slate-800">{{ $item['total_presentaciones'] }} finalistas</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Evaluadas:</span>
                                <span @class([
                                    'font-bold',
                                    'text-emerald-600' => $item['todas_evaluadas'] && $item['total_presentaciones'] > 0,
                                    'text-amber-600' => ! $item['todas_evaluadas'] || $item['total_presentaciones'] === 0,
                                ])>
                                    {{ $item['presentaciones_finalizadas'] }} / {{ $item['total_presentaciones'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Sección Jurado Secreto -->
                        <div class="mb-4">
                            <span class="text-xs font-semibold text-slate-600 block mb-1.5">Jurado Secreto Asignado:</span>
                            @if ($secreto)
                                <div class="flex items-center justify-between p-2.5 bg-purple-50/70 border border-purple-200 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                            🔒
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-purple-950">{{ $secreto->nombre }} {{ $secreto->paterno }}</p>
                                            <p class="text-[10px] text-purple-700">{{ $secreto->email }}</p>
                                        </div>
                                    </div>
                                    <details class="relative">
                                        <summary class="cursor-pointer text-[11px] font-semibold text-purple-700 hover:text-purple-900 list-none">
                                            Cambiar
                                        </summary>
                                        <div class="absolute right-0 top-6 w-60 bg-white border border-slate-200 shadow-xl rounded-xl p-3 z-20">
                                            <form method="POST" action="{{ route('admin.jurados.secreto.designar') }}" class="space-y-2">
                                                @csrf
                                                <input type="hidden" name="categoria_id" value="{{ $categoria->id }}">
                                                <label class="text-[11px] font-bold text-slate-700 block">Seleccionar nuevo jurado secreto:</label>
                                                <select name="usuario_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                                                    @foreach ($jurados as $j)
                                                        <option value="{{ $j->id }}" @selected($secreto->id === $j->id)>
                                                            {{ $j->nombre }} {{ $j->paterno }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="w-full py-1 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-lg">
                                                    Guardar Cambio
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </div>
                            @else
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                    <p class="text-xs text-amber-800 font-medium mb-2">⚠️ Sin jurado secreto designado</p>
                                    <form method="POST" action="{{ route('admin.jurados.secreto.designar') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="categoria_id" value="{{ $categoria->id }}">
                                        <select name="usuario_id" required class="w-full text-xs rounded-lg border-amber-300 focus:border-purple-500 focus:ring-purple-500">
                                            <option value="">Designar jurado secreto…</option>
                                            @foreach ($jurados as $j)
                                                <option value="{{ $j->id }}">{{ $j->nombre }} {{ $j->paterno }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2.5 py-1.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-lg shrink-0">
                                            Asignar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Sección de Preparación y Control del Podio en Vivo -->
                    @if ($estaRevelado)
                        <!-- 1. Cuadro de Anticipación de Ganadores para el Admin -->
                        @if (! empty($item['top3']))
                            <div class="p-3 bg-slate-900 border border-slate-700/80 rounded-2xl text-white space-y-2 shadow-md">
                                <div class="flex items-center justify-between border-b border-slate-700/80 pb-1.5">
                                    <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>👁️</span> Ganadores Oficiales (Vista Admin)
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-mono">Jurado Secreto incluido</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                                    @foreach ($item['top3'] as $ganador)
                                        @php
                                            $pos = $ganador['posicion'];
                                            $icono = match($pos) { 1 => '🥇 1°', 2 => '🥈 2°', 3 => '🥉 3°', default => "{$pos}°" };
                                            $color = match($pos) {
                                                1 => 'border-amber-400/80 bg-amber-500/15 text-amber-300',
                                                2 => 'border-slate-400/80 bg-slate-500/15 text-slate-200',
                                                3 => 'border-amber-700/80 bg-amber-800/15 text-amber-400',
                                                default => 'border-slate-700 bg-slate-800 text-slate-300'
                                            };
                                        @endphp
                                        <div class="p-2 rounded-xl border {{ $color }} flex flex-col justify-between">
                                            <div class="flex items-center justify-between gap-1 text-[11px] font-bold">
                                                <span>{{ $icono }}</span>
                                                <span class="font-mono text-xs font-black">{{ number_format($ganador['nota_final'] ?? 0, 2) }} pts</span>
                                            </div>
                                            <p class="text-xs font-semibold text-white truncate mt-1" title="{{ $ganador['nombre'] }}">
                                                {{ $ganador['nombre'] }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $ganador['departamento'] ?? 'Bolivia' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- 2. Botonera Secuencial de Revelación en Vivo -->
                        <div class="p-3 bg-purple-50/90 border border-purple-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-black text-purple-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🏆</span> Control de Revelación en Vivo
                                </span>
                                @php
                                    $pasoAct = $categoria->podioPasoActual();
                                    if ($categoria->tieneUnSoloGanador()) {
                                        $badgeTexto = match ($pasoAct) {
                                            0 => '0/1 Tapado (Misterio)',
                                            1 => '1/1 Ganador Revelado',
                                            default => 'Modo Ganador',
                                        };
                                    } else {
                                        $badgeTexto = match ($pasoAct) {
                                            0 => '0/3 Tapado (Misterio)',
                                            3 => '1/3 Revelado (3° Lugar)',
                                            2 => '2/3 Revelados (3° y 2°)',
                                            1 => '3/3 Completo (Podio Oficial)',
                                            default => 'Modo Podio',
                                        };
                                    }
                                @endphp
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-purple-200 text-purple-900 font-bold">
                                    {{ $badgeTexto }}
                                </span>
                            </div>

                            @if ($categoria->tieneUnSoloGanador())
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <!-- Botón Modo Misterio (Paso 0) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="0">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1',
                                            'bg-purple-900 text-white shadow-inner ring-2 ring-purple-400' => $pasoAct === 0,
                                            'bg-white border border-purple-300 text-purple-900 hover:bg-purple-100' => $pasoAct !== 0,
                                        ]) title="Voltear carta con signo ? y ocultar general">
                                            <span>🎭</span> 0: Tapar Carta
                                        </button>
                                    </form>

                                    <!-- Botón Revelar Ganador Oficial (Paso 1) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="1">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-black transition flex items-center justify-center gap-1',
                                            'bg-yellow-500 text-slate-950 font-black shadow-md ring-2 ring-yellow-400' => $pasoAct === 1,
                                            'bg-white border border-yellow-500 text-yellow-950 hover:bg-yellow-50' => $pasoAct !== 1,
                                        ]) title="Girar carta del ganador oficial y mostrar tabla general">
                                            <span>👑</span> 1: Revelar {{ $categoria->etiquetaGanador() }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 pt-1">
                                    <!-- Botón Modo Misterio (Paso 0) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="0">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1',
                                            'bg-purple-900 text-white shadow-inner ring-2 ring-purple-400' => $pasoAct === 0,
                                            'bg-white border border-purple-300 text-purple-900 hover:bg-purple-100' => $pasoAct !== 0,
                                        ]) title="Voltear todas las cartas con signo ? y ocultar general">
                                            <span>🎭</span> 0: Tapar Todo
                                        </button>
                                    </form>

                                    <!-- Botón Revelar 3° Lugar (Paso 3) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="3">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1',
                                            'bg-amber-700 text-white shadow-md ring-2 ring-amber-400' => $pasoAct === 3,
                                            'bg-white border border-amber-500 text-amber-900 hover:bg-amber-50' => $pasoAct !== 3,
                                        ]) title="Girar carta del 3° Lugar">
                                            <span>🥉</span> 1: Revelar 3°
                                        </button>
                                    </form>

                                    <!-- Botón Revelar 2° Lugar (Paso 2) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="2">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1',
                                            'bg-slate-700 text-white shadow-md ring-2 ring-slate-400' => $pasoAct === 2,
                                            'bg-white border border-slate-400 text-slate-900 hover:bg-slate-100' => $pasoAct !== 2,
                                        ]) title="Girar carta del 2° Lugar">
                                            <span>🥈</span> 2: Revelar 2°
                                        </button>
                                    </form>

                                    <!-- Botón Revelar 1° Lugar y General (Paso 1) -->
                                    <form method="POST" action="{{ route('admin.categorias.podio-paso', $categoria) }}">
                                        @csrf
                                        <input type="hidden" name="paso" value="1">
                                        <button type="submit" @class([
                                            'w-full py-2 px-2 rounded-lg text-xs font-black transition flex items-center justify-center gap-1',
                                            'bg-yellow-500 text-slate-950 font-black shadow-md ring-2 ring-yellow-400' => $pasoAct === 1,
                                            'bg-white border border-yellow-500 text-yellow-950 hover:bg-yellow-50' => $pasoAct !== 1,
                                        ]) title="Girar carta del 1° Lugar y mostrar tabla general">
                                            <span>🥇</span> 3: Revelar 1°
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-1 border-t border-purple-200/60">
                                <span class="text-[10px] text-purple-700">Transmitiendo en vivo a la vista pública</span>
                                <form method="POST" action="{{ route('admin.categorias.ocultar', $categoria) }}"
                                      onsubmit="return confirm('¿Volver a ocultar el podio y regresar a la vista de ranking normal?');">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-semibold text-purple-800 hover:text-purple-950 underline">
                                        Desactivar modo podio
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Botón Principal: Preparar Podio (Modo Misterio) -->
                        <div class="p-3 bg-purple-50/80 border border-purple-200 rounded-2xl space-y-2">
                            <div>
                                <h4 class="text-xs font-black text-purple-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🎭</span> Podio de Premiación en Vivo
                                </h4>
                                <p class="text-[11px] text-purple-700 mt-0.5">
                                    Prepara el terreno oficial calculando notas finales con jurado secreto y activando el podio misterio en pantalla pública.
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.categorias.preparar-podio', $categoria) }}"
                                  onsubmit="return confirm('¿Preparar el podio para {{ $categoria->categoria }}? La pantalla pública pasará a Modo Misterio con cartas tapadas.');">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-4 bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-700 hover:from-purple-600 hover:to-indigo-600 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5">
                                    <span>🎭</span>
                                    <span>Preparar Podio (Modo Misterio)</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Estado y Cierre Oficial de Calificaciones -->
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        @if (! $estaFinalizada)
                            <form method="POST" action="{{ route('admin.categorias.finalizar', $categoria) }}" class="w-full"
                                  onsubmit="return confirm('¿Cerrar oficialmente las calificaciones de {{ $categoria->categoria }}? Ya no se permitirán más registros ni correcciones.');">
                                @csrf
                                <button type="submit" class="w-full px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                                    <span>🏁</span>
                                    <span>Cerrar Calificaciones</span>
                                </button>
                            </form>
                        @else
                            <div class="flex items-center justify-between gap-2 w-full">
                                <span class="text-xs font-bold text-emerald-800 bg-emerald-100 border border-emerald-300 px-3 py-1.5 rounded-lg flex items-center gap-1.5 flex-1">
                                    <span>✓</span> Calificaciones Oficialmente Cerradas
                                </span>
                                <form method="POST" action="{{ route('admin.categorias.reabrir', $categoria) }}"
                                      onsubmit="return confirm('¿Reabrir las calificaciones de {{ $categoria->categoria }}?');">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-300 transition">
                                        ↺ Reabrir
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
