<x-layouts.jurado>
    @if (session('status') || session('success'))
        <div class="mb-4 p-3.5 rounded-2xl bg-[#0f2418] border border-emerald-500/40 text-emerald-200 text-xs sm:text-sm shadow-lg flex items-center gap-2.5 animate-fadeIn">
            <span class="text-emerald-400 text-base font-bold">✓</span>
            <span>{{ session('status') ?? session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3.5 rounded-2xl bg-[#2b080f] border border-rose-500/40 text-rose-200 text-xs sm:text-sm shadow-lg flex items-center gap-2.5 animate-fadeIn">
            <span class="text-rose-400 text-base font-bold">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $idInicialSeleccionado = $presentacionEnVivo?->id ?? ($presentacionesCategoria->first()?->id ?? 0);
    @endphp

    <div class="space-y-4 max-w-7xl mx-auto"
         id="panelJuradoRaiz"
         x-data="{
             selectedId: {{ $idInicialSeleccionado }},
             selectParticipant(id) {
                 this.selectedId = id;
             }
         }"
         data-categoria-activa-id="{{ $categoriaActiva?->id ?? '' }}"
         data-es-delegado="{{ ($esDelegadoMesa ?? false) ? '1' : '0' }}">

        <!-- ============================================================== -->
        <!-- 1. SLIDER / TRACK DE CATEGORÍAS (ESTILO SLANTED / GALA PRO)    -->
        <!-- ============================================================== -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            
            <!-- Botón Flecha Izquierda Slanted -->
            <button type="button"
                    onclick="scrollCategorias('left')"
                    title="Categorías anteriores"
                    class="shrink-0 -skew-x-12 transform px-3.5 sm:px-4 py-2.5 sm:py-3 bg-[#13040a] hover:bg-[#280715] text-[#d4af37] hover:text-amber-200 transition-all shadow-lg rounded-sm">
                <div class="skew-x-12 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>
            </button>

            <!-- Pista Scrollable de Categorías Slanted -->
            <div id="contenedorCategoriasSlider"
                 class="flex-1 flex items-center gap-1.5 sm:gap-2.5 overflow-x-auto py-1 scroll-smooth scrollbar-none"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach ($categorias as $cat)
                    @php
                        $esActiva = ($categoriaActiva?->id === $cat->id);
                        $finalizada = $cat->estaFinalizada();
                        $revelada = $cat->estaRevelada();
                        $nombreTruncado = \Illuminate\Support\Str::limit($cat->categoria, 27, '...');
                    @endphp

                    <div class="shrink-0">
                        @if ($esDelegadoMesa ?? false)
                            <form method="POST" action="{{ route('mesa.categorias.seleccionar', $cat) }}" class="inline-block">
                                @csrf
                                <button type="submit"
                                        title="{{ $cat->categoria }}"
                                        class="-skew-x-12 transform px-4 sm:px-5 py-2.5 transition-all duration-200 group rounded-sm
                                            {{ $esActiva 
                                                ? 'bg-gradient-to-r from-[#2a0610] via-[#3a0817] to-[#2a0610] border-t-2 border-b-2 border-amber-400 text-white shadow-[0_0_18px_rgba(251,191,36,0.4)]' 
                                                : 'bg-[#120409] hover:bg-[#200612] text-slate-300 hover:text-white' }}">
                                    
                                    <div class="skew-x-12 flex items-center gap-2 whitespace-nowrap">
                                        @if ($esActiva)
                                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_8px_#f59e0b] animate-pulse"></span>
                                        @endif

                                        <span class="font-extrabold text-xs sm:text-sm uppercase tracking-wider font-cinzel {{ $esActiva ? 'text-amber-200' : 'text-slate-300 group-hover:text-white' }}">
                                            {{ $nombreTruncado }}
                                        </span>

                                        @if ($finalizada)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-950/90 text-emerald-300 border border-emerald-500/50 shadow-sm font-mono">
                                                Cerrada
                                            </span>
                                        @elseif ($esActiva)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40 shadow-sm font-mono">
                                                Activa
                                            </span>
                                        @endif
                                    </div>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('jurado.inicio', ['cat' => $cat->id]) }}"
                               title="{{ $cat->categoria }}"
                               class="inline-block -skew-x-12 transform px-4 sm:px-5 py-2.5 transition-all duration-200 group rounded-sm
                                    {{ $esActiva 
                                        ? 'bg-gradient-to-r from-[#2a0610] via-[#3a0817] to-[#2a0610] border-t-2 border-b-2 border-amber-400 text-white shadow-[0_0_18px_rgba(251,191,36,0.4)]' 
                                        : 'bg-[#120409] hover:bg-[#200612] text-slate-300 hover:text-white' }}">
                                
                                <div class="skew-x-12 flex items-center gap-2 whitespace-nowrap">
                                    @if ($esActiva)
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_8px_#f59e0b] animate-pulse"></span>
                                    @endif

                                    <span class="font-extrabold text-xs sm:text-sm uppercase tracking-wider font-cinzel {{ $esActiva ? 'text-amber-200' : 'text-slate-300 group-hover:text-white' }}">
                                        {{ $nombreTruncado }}
                                    </span>

                                    @if ($finalizada)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-950/90 text-emerald-300 border border-emerald-500/50 shadow-sm font-mono">
                                            Cerrada
                                        </span>
                                    @elseif ($esActiva)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40 shadow-sm font-mono">
                                            Activa
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Botón Flecha Derecha Slanted -->
            <button type="button"
                    onclick="scrollCategorias('right')"
                    title="Categorías siguientes"
                    class="shrink-0 -skew-x-12 transform px-3.5 sm:px-4 py-2.5 sm:py-3 bg-[#13040a] hover:bg-[#280715] text-[#d4af37] hover:text-amber-200 transition-all shadow-lg rounded-sm">
                <div class="skew-x-12 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>

        </div>

        <!-- ============================================================== -->
        <!-- 2. BOTONES DE ACCIÓN DISTINGUIDOS (SOLO PARA JURADO DELEGADO)  -->
        <!-- ============================================================== -->
        @if ($esDelegadoMesa ?? false)
            <div class="flex flex-wrap items-center gap-3 pt-1">
                
                <!-- Botón 1: Cerrar Calificación -->
                @if ($categoriaActiva)
                    @if (! $categoriaActiva->estaFinalizada())
                        <form method="POST" action="{{ route('mesa.categorias.cerrar', $categoriaActiva) }}" onsubmit="return confirm('¿Está seguro de cerrar las calificaciones de la categoría {{ $categoriaActiva->categoria }}? Ya no se permitirán más registros ni correcciones.')" class="flex-1 sm:flex-initial">
                            @csrf
                            <button type="submit"
                                    class="-skew-x-12 transform px-5 py-2.5 bg-gradient-to-r from-[#4a0817] via-[#630b20] to-[#4a0817] hover:from-[#630b20] hover:via-[#7e0d29] hover:to-[#630b20] border border-rose-500/60 hover:border-rose-400 text-rose-100 hover:text-white shadow-[0_0_15px_rgba(225,29,72,0.25)] hover:shadow-[0_0_22px_rgba(225,29,72,0.45)] transition-all rounded-sm group w-full">
                                <div class="skew-x-12 flex items-center justify-center gap-2 whitespace-nowrap">
                                    <span class="text-xs">🔒</span>
                                    <span class="font-black text-xs uppercase tracking-wider text-white">
                                        Cerrar Calificación
                                    </span>
                                </div>
                            </button>
                        </form>
                    @else
                        <div class="-skew-x-12 transform px-5 py-2.5 bg-[#0c2317] border border-emerald-500/60 text-emerald-300 shadow-inner rounded-sm flex-1 sm:flex-initial">
                            <div class="skew-x-12 flex items-center justify-center gap-2 whitespace-nowrap">
                                <span class="text-xs">✓</span>
                                <span class="font-black text-xs uppercase tracking-wider">
                                    Calificación Cerrada
                                </span>
                            </div>
                        </div>
                    @endif
                @endif

            </div>
        @endif

        @if ($presentacionesCategoria->isEmpty())
            <div class="bg-[#0e0307]/80 rounded-2xl border border-[#6b0015]/40 p-16 text-center space-y-3 shadow-2xl">
                <span class="text-4xl text-[#d4af37]">🎵</span>
                <h3 class="text-base font-bold text-white font-cinzel">No hay participaciones para la categoría activa</h3>
                <p class="text-xs text-slate-400">Seleccione otra categoría en el slider superior para revisar sus participantes.</p>
            </div>
        @else

            <!-- ========================================================================= -->
            <!-- 3. SECCIÓN SUPERIOR CENTRAL: CARD DESTACADO DEL PARTICIPANTE EN ESCENARIO -->
            <!-- ========================================================================= -->
            <div class="flex justify-center py-2">
                @foreach ($presentacionesCategoria as $pres)
                    @php
                        $enEscenario = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO);
                        $participando = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO);
                        $finalizado = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::FINALIZADO);
                        $porParticipar = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR);
                        $esEnVivo = ($enEscenario || $participando);

                        $cronometro = app(\App\Modules\Presentaciones\Services\GestionarCronometroService::class)->obtenerEstado($pres);
                        $corriendo = $cronometro['corriendo'] ?? false;
                        $segundosRestantes = $cronometro['segundos_restantes'] ?? (($pres->tiempo_maximo ?? 10) * 60);
                        $tiempoMaximo = $pres->tiempo_maximo ?? 10;

                        $juradoActual = $pres->jurados->firstWhere('id_usuario', auth()->id());
                        $miCalificacion = $juradoActual?->calificacion;
                        $yaCalifique = ($miCalificacion && $miCalificacion->esEmitida());

                        $juradosVotaron = $pres->jurados->filter(fn ($j) => $j->calificacion && $j->calificacion->esEmitida())->count();
                        $totalMesa = $pres->jurados->count();
                        $catEstaCerrada = (bool) ($categoriaActiva?->estaFinalizada());
                        $departamentoNombre = $pres->inscripcion?->departamento?->nombre ?? 'Bolivia';
                    @endphp

                    <!-- Card Superior Central con Proporciones Exactas de Gala -->
                    <div x-show="selectedId === {{ $pres->id }}"
                         x-cloak
                         id="moduloCentroEnVivo-{{ $pres->id }}"
                         class="w-[280px] sm:w-[300px] shrink-0 transition-all duration-300"
                         data-presentacion-id="{{ $pres->id }}"
                         data-cronometro-corriendo="{{ $corriendo ? '1' : '0' }}"
                         data-cronometro-iniciado-at="{{ $cronometro['cronometro_iniciado_at'] ?? $pres->cronometro_iniciado_at?->toIso8601String() ?? '' }}"
                         data-cronometro-finalizado-at="{{ $cronometro['cronometro_finalizado_at'] ?? $pres->cronometro_finalizado_at?->toIso8601String() ?? '' }}"
                         data-segundos-restantes="{{ $segundosRestantes }}"
                         data-tiempo-maximo="{{ $tiempoMaximo }}">

                        <!-- Contenedor con Aspect Ratio 3/4 y Bordes Finos de Gala -->
                        <div class="relative w-full aspect-[3/4] bg-gradient-to-b from-[#18050e] via-[#0b0207] to-[#050103] rounded-2xl border-2 {{ $esEnVivo ? 'border-amber-400 shadow-[0_0_35px_rgba(212,175,55,0.45)]' : ($finalizado ? 'border-emerald-500/60 shadow-xl' : 'border-[#6b0015]/60 hover:border-amber-400/80 shadow-2xl') }} p-4 sm:p-5 flex flex-col justify-between overflow-hidden relative group">
                            
                            <!-- Esquineros Dorados Finos -->
                            <div class="corner-bracket-tl"></div>
                            <div class="corner-bracket-tr"></div>
                            <div class="corner-bracket-bl"></div>
                            <div class="corner-bracket-br"></div>

                            <!-- Fondo / Fotografía o Logo Oficial del Evento -->
                            <div class="absolute inset-0 z-0 overflow-hidden flex items-center justify-center bg-[#050103]">
                                @if ($pres->fotoSinFondoUrl())
                                    <div class="w-full h-full flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_center,rgba(212,175,55,0.2),rgba(5,1,3,0.95)_75%)]">
                                        <img src="{{ $pres->fotoSinFondoUrl() }}"
                                             alt="{{ $pres->nombreMostrado() }}"
                                             class="max-h-full max-w-full object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,0.9)] transition duration-700 group-hover:scale-105">
                                    </div>
                                @elseif ($pres->fotoUrl())
                                    <img src="{{ $pres->fotoUrl() }}"
                                         alt="{{ $pres->nombreMostrado() }}"
                                         class="w-full h-full object-cover object-top transition duration-700 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(180,83,9,0.35),rgba(5,1,3,0.95)_70%)] p-6">
                                        <img src="/images/logo-oficial.png"
                                             alt="Aquí Canta Bolivia"
                                             class="h-24 w-24 object-contain opacity-90 group-hover:opacity-100 transition duration-500 drop-shadow-[0_0_20px_rgba(212,175,55,0.5)]"
                                             onerror="this.src='/images/logo-oficial.png'">
                                        <span class="text-[9px] uppercase tracking-[0.25em] text-[#d4af37]/80 font-cinzel font-bold mt-2.5">Festival 2026</span>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[#050103] via-[#050103]/80 to-transparent pointer-events-none"></div>
                            </div>

                            <!-- Header Superior del Card Central con Cronómetro en la Esquina Superior -->
                            <div class="relative z-10 flex items-center justify-between gap-2 border-b border-[#3b000b]/80 pb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-semibold text-slate-400 font-mono">
                                        #{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    
                                    @if ($enEscenario)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-600 text-white border border-rose-400 animate-pulse shadow">
                                            Escenario
                                        </span>
                                    @elseif ($participando)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-600 text-white border border-emerald-400 shadow flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                            En Vivo
                                        </span>
                                    @elseif ($finalizado)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#1a050d] text-emerald-300 border border-emerald-500/40">
                                            ✓ Fin
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-black/80 text-slate-400 border border-slate-700">
                                            Espera
                                        </span>
                                    @endif
                                </div>

                                <!-- Esquina Superior Derecha: Cronómetro si está En Vivo / Categoría si no -->
                                <div>
                                    @if ($esEnVivo)
                                        <div class="inline-flex items-baseline gap-1 px-2.5 py-0.5 rounded-lg bg-black/90 border border-amber-400/80 shadow-[0_0_12px_rgba(251,191,36,0.5)]">
                                            <span class="text-sm font-black font-mono tracking-tight text-amber-300" data-cronometro-display>
                                                {{ gmdate('i:s', $segundosRestantes) }}
                                            </span>
                                            <span class="text-[10px] font-bold font-mono text-slate-400">
                                                / {{ $tiempoMaximo }}:00
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.15em] text-[#d4af37]/90 font-cinzel truncate max-w-[110px]" title="{{ $categoriaActiva?->categoria }}">
                                            {{ $categoriaActiva?->categoria }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Información Inferior y Acciones de Calificación -->
                            <div class="relative z-10 space-y-2 text-center pt-6">
                                
                                <div class="w-8 h-[1.5px] bg-[#d4af37]/60 mx-auto"></div>

                                <div class="space-y-0.5">
                                    <h3 class="text-base sm:text-lg font-bold text-[#fef9c3] tracking-[0.08em] uppercase font-cinzel group-hover:text-[#d4af37] transition line-clamp-1"
                                        title="{{ $pres->nombreMostrado() }}">
                                        {{ $pres->nombreMostrado() }}
                                    </h3>
                                    
                                    @php
                                        $departamentoBandera = $pres->inscripcion?->departamento?->bandera_url ?? \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($departamentoNombre);
                                    @endphp
                                    <div class="flex items-center justify-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300">
                                        @if ($departamentoBandera)
                                            <img src="{{ $departamentoBandera }}" alt="{{ $departamentoNombre }}" class="h-3 w-auto object-cover rounded-[2px] shadow-sm">
                                        @else
                                            <span>🇧🇴</span>
                                        @endif
                                        <span>{{ $departamentoNombre }}</span>
                                    </div>

                                    @if ($pres->cancion_tema)
                                        <p class="text-[10px] text-amber-300/90 italic truncate" title="{{ $pres->cancion_tema }}">
                                            Tema: "{{ $pres->cancion_tema }}"
                                        </p>
                                    @endif
                                </div>

                                <!-- Botón Principal de Acción -->
                                <div class="pt-2 border-t border-[#3b000b]/80 space-y-1.5">
                                    
                                    @if ($esEnVivo)
                                        @if ($catEstaCerrada)
                                            <div class="w-full py-2 px-3 rounded-xl bg-slate-950/80 border border-slate-800 text-slate-400 font-bold text-xs uppercase tracking-wider text-center cursor-not-allowed">
                                                🔒 Calificaciones Cerradas
                                            </div>
                                        @else
                                            <a href="{{ route('jurado.presentaciones.actual') }}"
                                               class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs uppercase tracking-wider shadow-[0_0_20px_rgba(16,185,129,0.4)] flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5">
                                                <span>⭐</span>
                                                <span>{{ $yaCalifique ? 'Modificar mi Nota' : 'Calificar en Vivo' }}</span>
                                            </a>
                                        @endif

                                    @elseif ($finalizado)
                                        
                                        @if ($catEstaCerrada)
                                            <div class="w-full py-2 px-3 rounded-xl bg-slate-950/80 border border-slate-800 text-slate-400 font-semibold text-xs text-center flex items-center justify-center gap-1 cursor-not-allowed">
                                                <span>🔒</span>
                                                <span>Calificaciones Cerradas</span>
                                            </div>
                                        @elseif ($yaCalifique)
                                            <a href="{{ route('jurado.calificaciones.corregir.form', $pres) }}"
                                               class="w-full py-2 px-3 rounded-xl bg-[#260a15] hover:bg-[#3d0f22] text-amber-300 font-bold text-xs border border-amber-500/40 text-center transition shadow flex items-center justify-center gap-1.5">
                                                <span>✏️</span>
                                                <span>Corregir Nota ({{ number_format($miCalificacion->nota_total, 2) }} pts)</span>
                                            </a>
                                        @else
                                            <a href="{{ route('jurado.presentaciones.actual', ['presentacion_id' => $pres->id]) }}"
                                               class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs text-center transition shadow flex items-center justify-center gap-1.5">
                                                <span>⭐</span>
                                                <span>Calificar Participante</span>
                                            </a>
                                        @endif

                                    @else

                                        @if (($esDelegadoMesa ?? false) && ! $presentacionesCategoria->contains(fn ($p) => in_array($p->estado, [\App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO, \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO], true)))
                                            <form method="POST" action="{{ route('mesa.presentaciones.transicion', $pres) }}" class="w-full">
                                                @csrf
                                                <input type="hidden" name="estado" value="{{ \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value }}">
                                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 text-xs font-black transition shadow flex items-center justify-center gap-1.5">
                                                    <span>📢</span>
                                                    <span>Llamar al Escenario</span>
                                                </button>
                                            </form>
                                        @else
                                            <div class="w-full py-2 px-3 rounded-xl bg-slate-950/80 border border-slate-900 text-slate-400 text-xs font-semibold text-center flex items-center justify-center gap-1">
                                                <span>🔒</span>
                                                <span>En espera de su turno</span>
                                            </div>
                                        @endif

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach
            </div>

            <!-- ========================================================================= -->
            <!-- 4. SECCIÓN INFERIOR: MESA DE PARTICIPANTES (ESTILO RETRATOS DE GALA)     -->
            <!-- ========================================================================= -->
            <div class="space-y-3 pt-3">
                
                <div class="flex items-center justify-between px-1">
                    <h3 class="font-cinzel font-bold text-xs sm:text-sm text-[#d4af37] uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Mesa de Participantes ({{ $presentacionesCategoria->count() }}) — Haga clic para enfocar</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-medium">
                        Fase Final · Oruro 2026
                    </span>
                </div>

                <!-- Grid de Tarjetas Retrato (Aspect Ratio 3/4 idéntico al Ranking Público) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 sm:gap-5 justify-items-center">
                    @foreach ($presentacionesCategoria as $idx => $pres)
                        @php
                            $enEscenario = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO);
                            $participando = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO);
                            $finalizado = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::FINALIZADO);
                            $porParticipar = ($pres->estado === \App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR);
                            $esEnVivo = ($enEscenario || $participando);

                            $juradoAsignadoActual = $pres->jurados->firstWhere('id_usuario', auth()->id());
                            $miCalificacion = $juradoAsignadoActual?->calificacion;
                            $heCalificado = ($miCalificacion && $miCalificacion->esEmitida());

                            $departamentoNombre = $pres->inscripcion?->departamento?->nombre ?? 'Bolivia';
                        @endphp

                        <!-- TARJETA RETRATO COMPACTA (ASPECT RATIO 3/4) -->
                        <div @click="selectParticipant({{ $pres->id }})"
                             class="relative w-full max-w-[290px] aspect-[3/4] bg-gradient-to-b from-[#14040a] via-[#090205] to-[#050103] rounded-2xl p-4 shadow-2xl flex flex-col justify-between overflow-hidden group transition-all duration-500 cursor-pointer"
                             :class="selectedId === {{ $pres->id }} ? 'border-2 border-[#d4af37] shadow-[0_0_25px_rgba(212,175,55,0.4)] scale-[1.02]' : 'border border-[#6b0015]/40 hover:border-[#d4af37]/80 hover:shadow-2xl hover:shadow-[#800020]/25'">
                            
                            <!-- Esquineros Dorados Finos -->
                            <div class="corner-bracket-tl"></div>
                            <div class="corner-bracket-tr"></div>
                            <div class="corner-bracket-bl"></div>
                            <div class="corner-bracket-br"></div>

                            <!-- Fondo / Fotografía o Logo Oficial del Participante -->
                            <div class="absolute inset-0 z-0 overflow-hidden flex items-center justify-center bg-[#050103]">
                                @if ($pres->fotoSinFondoUrl())
                                    <div class="w-full h-full flex items-center justify-center p-3 bg-[radial-gradient(ellipse_at_center,rgba(128,0,32,0.35),rgba(5,1,3,0.95)_75%)]">
                                        <img src="{{ $pres->fotoSinFondoUrl() }}"
                                             alt="{{ $pres->nombreMostrado() }}"
                                             class="max-h-full max-w-full object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.85)] transition duration-700 group-hover:scale-105">
                                    </div>
                                @elseif ($pres->fotoUrl())
                                    <img src="{{ $pres->fotoUrl() }}"
                                         alt="{{ $pres->nombreMostrado() }}"
                                         class="w-full h-full object-cover object-top transition duration-700 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(128,0,32,0.4),rgba(5,1,3,0.95)_70%)] p-6">
                                        <img src="/images/logo-oficial.png"
                                             alt="Aquí Canta Bolivia"
                                             class="h-24 w-24 object-contain opacity-80 group-hover:opacity-100 transition duration-500"
                                             onerror="this.src='/images/logo-oficial.png'">
                                        <span class="text-[9px] uppercase tracking-[0.25em] text-[#d4af37]/80 font-cinzel font-bold mt-3">Festival 2026</span>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[#050103] via-[#050103]/75 to-transparent pointer-events-none"></div>
                            </div>

                            <!-- Header Superior del Card -->
                            <div class="relative z-10 flex items-center justify-between gap-2 border-b border-[#3b000b]/80 pb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-semibold text-slate-400 font-mono">
                                        #{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    @if ($enEscenario)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-600 text-white border border-rose-400 animate-pulse shadow">
                                            Escenario
                                        </span>
                                    @elseif ($participando)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-600 text-white border border-emerald-400 shadow">
                                            En Vivo
                                        </span>
                                    @elseif ($finalizado)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#1a050d] text-emerald-300 border border-emerald-500/40">
                                            ✓ Fin
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-black/70 text-slate-400 border border-slate-800">
                                            Espera
                                        </span>
                                    @endif
                                </div>

                                <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.2em] text-[#d4af37]/90 font-cinzel truncate max-w-[130px]" title="{{ $categoriaActiva?->categoria }}">
                                    {{ $categoriaActiva?->categoria }}
                                </span>
                            </div>

                            <!-- Información Inferior -->
                            <div class="relative z-10 space-y-2 text-center pt-8">
                                
                                <div class="w-6 h-[1.5px] bg-[#d4af37]/50 mx-auto"></div>

                                <div class="space-y-0.5">
                                    <h3 class="text-base sm:text-lg font-bold text-[#fef9c3] tracking-[0.08em] uppercase font-cinzel group-hover:text-[#d4af37] transition line-clamp-1"
                                        title="{{ $pres->nombreMostrado() }}">
                                        {{ $pres->nombreMostrado() }}
                                    </h3>
                                    
                                    @php
                                        $departamentoBandera = $pres->inscripcion?->departamento?->bandera_url ?? \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($departamentoNombre);
                                    @endphp
                                    <div class="flex items-center justify-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300">
                                        @if ($departamentoBandera)
                                            <img src="{{ $departamentoBandera }}" alt="{{ $departamentoNombre }}" class="h-3 w-auto object-cover rounded-[2px] shadow-sm">
                                        @else
                                            <span>🇧🇴</span>
                                        @endif
                                        <span>{{ $departamentoNombre }}</span>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-[#3b000b]/80 flex items-center justify-between gap-2">
                                    <div class="text-left font-mono">
                                        @if ($finalizado)
                                            @if ($heCalificado)
                                                <span class="text-sm font-black gold-gradient-text tabular-nums">
                                                    {{ number_format($miCalificacion->nota_total, 2) }}
                                                </span>
                                                <span class="text-[8px] text-slate-500">pts</span>
                                            @else
                                                <span class="text-[10px] text-rose-400 font-sans uppercase">
                                                    Sin nota
                                                </span>
                                            @endif
                                        @elseif ($esEnVivo)
                                            <span class="text-[10px] text-amber-400 font-sans uppercase">
                                                En curso
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-500 font-sans uppercase">
                                                Espera
                                            </span>
                                        @endif
                                    </div>

                                    <span class="px-2 py-0.5 rounded bg-[#1a050d] border border-[#6b0015] group-hover:border-amber-400/60 text-amber-200/90 text-[10px] font-semibold uppercase tracking-wider transition">
                                        {{ $esEnVivo ? '⭐ Evaluar' : ($finalizado ? '✏️ Ver Nota' : 'Enfocar') }}
                                    </span>
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

        @endif

    </div>

    @push('scripts')
    <script>
        function scrollCategorias(direction) {
            const container = document.getElementById('contenedorCategoriasSlider');
            if (!container) return;
            const scrollAmount = 260;
            if (direction === 'left') {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }

        // =========================================================================
        // Temporizador de Cronómetro en Vivo para los Cards
        // =========================================================================
        function formatearTiempo(seg) {
            seg = Math.max(0, Math.floor(seg));
            const m = Math.floor(seg / 60).toString().padStart(2, '0');
            const s = (seg % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }

        function actualizarCronometrosEnVivo() {
            document.querySelectorAll('[data-cronometro-corriendo="1"]').forEach(card => {
                const iniciadoAt = card.dataset.cronometroIniciadoAt;
                const tiempoMaxMin = parseInt(card.dataset.tiempoMaximo, 10) || 10;
                const tiempoMaxSeg = tiempoMaxMin * 60;
                const display = card.querySelector('[data-cronometro-display]');
                if (!display) return;

                if (iniciadoAt) {
                    const startTime = new Date(iniciadoAt).getTime();
                    const now = Date.now();
                    const transcurrido = Math.floor((now - startTime) / 1000);
                    const restante = Math.max(0, tiempoMaxSeg - transcurrido);
                    display.textContent = formatearTiempo(restante);
                }
            });
        }

        setInterval(actualizarCronometrosEnVivo, 1000);
        actualizarCronometrosEnVivo();

        // =========================================================================
        // Sincronización en Tiempo Real del Panel de Jurados (Laravel Echo / Reverb)
        // =========================================================================
        (function () {
            function suscribirEcho() {
                if (!window.Echo) return false;

                try {
                    // Escuchar canal público y privado de presentaciones
                    window.Echo.channel('presentaciones')
                        .listen('.presentacion.estado.cambiado', function (datos) {
                            window.location.reload();
                        })
                        .listen('.cronometro.actualizado', function (datos) {
                            const presId = datos.presentacion_id;
                            const card = document.querySelector(`[data-presentacion-id="${presId}"]`);
                            if (card) {
                                card.dataset.cronometroCorriendo = datos.corriendo ? '1' : '0';
                                card.dataset.cronometroIniciadoAt = datos.cronometro_iniciado_at || '';
                                card.dataset.cronometroFinalizadoAt = datos.cronometro_finalizado_at || '';
                                card.dataset.segundosRestantes = datos.segundos_restantes;
                                const display = card.querySelector('[data-cronometro-display]');
                                if (display && !datos.corriendo) {
                                    display.textContent = formatearTiempo(datos.segundos_restantes);
                                }
                                actualizarCronometrosEnVivo();
                            } else {
                                window.location.reload();
                            }
                        })
                        .listen('.calificacion.registrada', function (datos) {
                            window.location.reload();
                        });

                    // Escuchar canal de ranking y categorías
                    window.Echo.channel('ranking')
                        .listen('.categoria.activa.cambiada', function () {
                            window.location.reload();
                        })
                        .listen('.categoria.finalizada', function () {
                            window.location.reload();
                        })
                        .listen('.jurado.secreto.revelado', function () {
                            window.location.reload();
                        });

                    return true;
                } catch (e) {
                    console.warn('Error configurando WebSocket para el panel del jurado:', e);
                    return false;
                }
            }

            if (!suscribirEcho()) {
                let intentos = 0;
                const timerId = setInterval(function () {
                    intentos++;
                    if (suscribirEcho() || intentos > 20) {
                        clearInterval(timerId);
                    }
                }, 300);
            }
        })();
    </script>
    @endpush
</x-layouts.jurado>