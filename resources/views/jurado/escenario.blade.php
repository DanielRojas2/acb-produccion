<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'ACB Canta Bolivia') }} — Escenario</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700&family=Orbitron:wght@700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html, body {
                min-height: 100vh;
                min-height: 100dvh;
            }
            .font-cinzel { font-family: 'Cinzel', serif !important; }
            .font-orbitron { font-family: 'Orbitron', monospace !important; }
            .gold-gradient-text {
                background: linear-gradient(135deg, #FFF8DC 0%, #F5D061 40%, #D4AF37 75%, #AA7C11 100%) !important;
                -webkit-background-clip: text !important;
                -webkit-text-fill-color: transparent !important;
            }
            /* Scrollbar estilizada y no invasiva para secciones internas si fuera necesario */
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(15, 23, 42, 0.4);
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(217, 119, 6, 0.4);
                border-radius: 4px;
            }
            input[type=range].festival-slider {
                height: 11px !important;
                background: linear-gradient(90deg, #dc2626 0%, #eab308 50%, #16a34a 100%) !important;
            }
            input[type=range].festival-slider::-moz-range-track {
                height: 11px !important;
                background: linear-gradient(90deg, #dc2626 0%, #eab308 50%, #16a34a 100%) !important;
            }
            input[type=range].festival-slider::-webkit-slider-thumb {
                width: 25px !important;
                height: 25px !important;
            }
            input[type=range].festival-slider::-moz-range-thumb {
                width: 25px !important;
                height: 25px !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#030712] text-white flex flex-col justify-between select-none"
          data-escenario-raiz
          data-escenario-id="{{ $presentacion->id ?? '' }}"
          data-estado-actual="{{ $presentacion?->estado?->value ?? '' }}"
          data-cronometro-corriendo="{{ ($cronometro['corriendo'] ?? false) ? '1' : '0' }}"
          data-cronometro-iniciado-at="{{ $cronometro['cronometro_iniciado_at'] ?? $presentacion?->cronometro_iniciado_at?->toIso8601String() ?? '' }}"
          data-cronometro-finalizado-at="{{ $cronometro['cronometro_finalizado_at'] ?? $presentacion?->cronometro_finalizado_at?->toIso8601String() ?? '' }}"
          data-cronometro-iniciado-at-ms="{{ $cronometro['cronometro_iniciado_at_ms'] ?? $presentacion?->cronometro_iniciado_at?->valueOf() ?? '' }}"
          data-cronometro-finalizado-at-ms="{{ $cronometro['cronometro_finalizado_at_ms'] ?? $presentacion?->cronometro_finalizado_at?->valueOf() ?? '' }}"
          data-server-now-ms="{{ $cronometro['server_now_ms'] ?? (int) (microtime(true) * 1000) }}"
          data-segundos-restantes="{{ $cronometro['segundos_restantes'] ?? (($presentacion->tiempo_maximo ?? 10) * 60) }}"
          data-tiempo-maximo="{{ $presentacion->tiempo_maximo ?? 10 }}">

        <!-- Barra Superior Gala -->
        <header class="px-4 py-2 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-md flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="font-cinzel text-xs sm:text-sm font-bold tracking-widest uppercase text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-500">
                    Aquí Canta Bolivia
                </span>
                <span class="text-slate-700 font-light">|</span>
                <span class="text-[11px] sm:text-xs tracking-widest text-slate-400 font-semibold uppercase hidden xs:inline">
                    XXXIII Festival Nacional
                </span>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <span class="text-xs text-slate-400 hidden md:inline">
                        {{ auth()->user()->nombre }} <span class="text-slate-600">({{ auth()->user()->rol->value }})</span>
                    </span>
                    <a href="{{ auth()->user()->esJurado() ? route('jurado.inicio') : route('admin.inicio') }}"
                       class="text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-900 px-2.5 py-1 rounded-md border border-slate-700/80 transition shadow-sm">
                        Panel
                    </a>
                @endauth
                <!-- Botón Volver atrás -->
                <a href="javascript:history.back()"
                   onclick="if (window.history.length > 1) { window.history.back(); return false; } else { window.location.href='{{ auth()->check() && auth()->user()->esJurado() ? route('jurado.inicio') : route('admin.inicio') }}'; return false; }"
                   class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-300 hover:text-amber-200 bg-slate-900/90 hover:bg-slate-800 px-2.5 sm:px-3 py-1 rounded-lg border border-amber-500/40 hover:border-amber-400 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver atrás</span>
                </a>
            </div>
        </header>

        <!-- Contenedor Principal (2 Columnas) -->
        <main class="flex-1 flex flex-col justify-start px-4 sm:px-6 pt-4 pb-3 overflow-y-auto min-h-0 custom-scrollbar">
            
            {{-- Presentación Activa --}}
            <div data-escenario-activo class="{{ $presentacion !== null ? 'w-full max-w-7xl mx-auto flex flex-col justify-start min-h-0' : 'hidden w-full max-w-7xl mx-auto flex flex-col justify-start min-h-0' }}">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 items-start min-h-0">
                    
                    <!-- ========================================== -->
                    <!-- COLUMNA IZQUIERDA: INFORMACIÓN Y CRONÓMETRO -->
                    <!-- ========================================== -->
                    <div class="lg:col-span-6 flex flex-col justify-start pt-0 px-2 min-h-0">
                        
                        <!-- Header info del participante (Nombre + Badge debajo) -->
                        <div class="flex flex-col items-start w-full shrink-0">
                            <!-- Nombre del Participante (Dos tonos: blanco + dorado festival) -->
                            @php
                                $nombreCompleto = $presentacion?->nombreMostrado() ?? '';
                                $palabras = preg_split('/\s+/', trim($nombreCompleto));
                                $primerPalabra = array_shift($palabras) ?? '';
                                $restoPalabras = implode(' ', $palabras);
                            @endphp
                            <h1 data-nombre class="text-3xl sm:text-4xl xl:text-5xl font-extrabold uppercase tracking-tight font-cinzel leading-none m-0 p-0">
                                <span class="text-white drop-shadow-[0_2px_10px_rgba(255,255,255,0.2)]">{{ $primerPalabra }}</span>
                                @if ($restoPalabras !== '')
                                    <span class="gold-gradient-text drop-shadow-[0_0_20px_rgba(229,192,91,0.5)] font-black">{{ $restoPalabras }}</span>
                                @endif
                            </h1>

                            <!-- Badge de Estado (Ubicado DEBAJO del nombre) -->
                            <div data-estado class="mt-2 inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold tracking-wider uppercase {{ ($presentacion?->estado?->value === App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO->value) ? 'bg-emerald-950/70 text-emerald-400 border border-emerald-500/40 shadow-[0_0_12px_rgba(16,185,129,0.25)]' : (($presentacion?->estado?->value === App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value) ? 'bg-emerald-950/70 text-emerald-400 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.2)]' : 'bg-slate-900/80 text-slate-300 border border-slate-700') }}">
                                <span data-estado-dot class="w-2 h-2 rounded-full {{ ($presentacion?->estado?->value === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value) ? 'bg-amber-400 shadow-[0_0_8px_#fbbf24]' : 'bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse' }}"></span>
                                <span data-estado-texto>{{ $presentacion?->estado?->label() ?? 'En escenario' }}</span>
                            </div>
                        </div>

                        <!-- Fotografía / Imagen Hero del Artista (Agrandada dramáticamente al liberar espacio) -->
                        @php
                            $fotoHero = $presentacion?->fotoJuradoUrl() ?: $presentacion?->fotoUrl();
                        @endphp
                        <div class="relative my-1 sm:my-2 flex justify-center items-center w-full min-h-[240px] max-h-[48vh] sm:max-h-[52vh] lg:max-h-[56vh] shrink">
                            <!-- Resplandor sutil de fondo de escenario -->
                            <div class="absolute inset-0 max-w-md mx-auto rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

                            @if ($fotoHero)
                                <img src="{{ $fotoHero }}"
                                     alt="{{ $presentacion?->nombreMostrado() }}"
                                     class="relative z-10 max-h-[46vh] sm:max-h-[50vh] lg:max-h-[54vh] w-auto max-w-full object-contain filter drop-shadow-[0_20px_35px_rgba(0,0,0,0.95)] transition-transform duration-300">
                            @else
                                <!-- Silueta o símbolo musical elegante si no hay foto cargada -->
                                <div class="relative z-10 flex flex-col items-center justify-center p-6 rounded-3xl bg-slate-900/40 border border-slate-800/80 shadow-2xl backdrop-blur-sm">
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500/20 to-slate-800/50 flex items-center justify-center border border-amber-500/30 text-amber-300 shadow-inner">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                                        </svg>
                                    </div>
                                    <span class="mt-2.5 text-xs font-bold text-amber-300/80 uppercase tracking-widest font-cinzel">Aquí Canta Bolivia</span>
                                </div>
                            @endif

                            @if ($presentacion?->inscripcion?->participantes->first()?->pseudonimo)
                                <div class="absolute bottom-1 right-4 z-20">
                                    <span class="text-[11px] text-white/80 font-medium italic drop-shadow">
                                        "{{ $presentacion->inscripcion->participantes->first()->pseudonimo }}"
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Tag Informativo de Departamento (si aplica) -->
                        @if ($presentacion?->inscripcion?->departamento)
                            <div class="flex items-center justify-center flex-wrap gap-2 my-1 shrink-0">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0d1424]/90 border border-slate-800 text-slate-300 shadow-sm text-[11px] font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
                                    <span>{{ $presentacion->inscripcion->departamento->nombre }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Cronómetro Digital Neón y Controles de Acción -->
                        <div class="mt-2 flex items-center justify-start sm:justify-center gap-4 sm:gap-6 flex-wrap shrink-0">
                            <!-- Dígitos LED Neón Verde -->
                            <div class="flex items-center">
                                <span data-cronometro-display
                                      class="font-orbitron text-3xl sm:text-4xl lg:text-5xl font-black tabular-nums tracking-widest text-[#00ff88] drop-shadow-[0_0_20px_rgba(0,255,136,0.65)] select-none">
                                    {{ gmdate('i:s', $cronometro['segundos_restantes'] ?? (($presentacion->tiempo_maximo ?? 10) * 60)) }}
                                </span>
                                <!-- Elemento oculto para compatibilidad con Echo -->
                                <span class="sr-only" data-tiempo-max-display>/ {{ $presentacion->tiempo_maximo ?? 10 }}:00</span>
                            </div>

                            <!-- Controles de Cronómetro para Jurado Controlador / Admin -->
                            @if (($puedeControlar ?? false) && $presentacion !== null)
                                <div data-control-tiempo-caja
                                     data-url-iniciar="{{ route('presentaciones.cronometro.iniciar', $presentacion) }}"
                                     data-url-detener="{{ route('presentaciones.cronometro.detener', $presentacion) }}"
                                     class="flex items-center">
                                    @if ($presentacion->cronometroCorriendo())
                                        <form method="POST" action="{{ route('presentaciones.cronometro.detener', $presentacion) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-rose-950/40 border border-rose-400/30 transition flex items-center gap-2 cursor-pointer">
                                                <span>⏹</span>
                                                <span>Detener cronómetro</span>
                                            </button>
                                        </form>
                                    @elseif ($presentacion->cronometro_iniciado_at === null)
                                        <form method="POST" action="{{ route('presentaciones.cronometro.iniciar', $presentacion) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-emerald-950/40 border border-emerald-400/30 transition flex items-center gap-2 cursor-pointer">
                                                <span>▶</span>
                                                <span>Iniciar cronómetro</span>
                                            </button>
                                        </form>
                                    @else
                                        <span data-control-estado-texto class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 font-semibold">Cronómetro finalizado</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Elementos ocultos accesibles para WebSocket y compatibilidad -->
                        <p class="sr-only" data-categoria-fase>
                            {{ $presentacion?->inscripcion?->categoria?->categoria ?? '' }} · {{ $presentacion?->inscripcion?->fase?->fase?->label() ?? '' }}
                        </p>
                        <p class="sr-only" data-tiempo-max-texto>
                            Tiempo máximo de presentación: {{ $presentacion->tiempo_maximo ?? 10 }} minuto(s)
                        </p>

                    </div>

                    <!-- ========================================== -->
                    <!-- COLUMNA DERECHA: PARÁMETROS DE CALIFICACIÓN -->
                    <!-- ========================================== -->
                    <div class="lg:col-span-6 flex flex-col justify-start">
                        
                        @if (auth()->check() && auth()->user()->esJurado())
                            
                            @if ($juradoPresentacion)
                                {{-- Jurado Asignado --}}

                                @if ($calificacion && $calificacion->esEmitida())
                                    <!-- Caso: Calificación ya emitida -->
                                    <div class="bg-slate-900/80 rounded-2xl border border-slate-800 p-5 sm:p-6 space-y-4 shadow-xl">
                                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="text-emerald-400 font-bold text-xl">✓</span>
                                                <h3 class="text-[19px] sm:text-[20px] font-bold text-white">Calificación Registrada</h3>
                                            </div>
                                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[15px] font-bold rounded-full">
                                                Emitida
                                            </span>
                                        </div>

                                        <div class="bg-slate-950 rounded-xl p-4 text-center flex items-center justify-around">
                                            <span class="text-[17px] text-slate-300 uppercase font-bold tracking-wider">CALIFICACIÓN TOTAL:</span>
                                            <span class="text-4xl sm:text-5xl font-black text-emerald-400 font-mono tabular-nums">
                                                {{ number_format($calificacion->nota_total, 2) }}
                                                <span class="text-[17px] text-slate-400 font-normal">/ 100 pts</span>
                                            </span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach ($calificacion->detalles as $det)
                                                @php
                                                    $cp = $categoriaParametros->firstWhere('id_parametro', $det->id_parametro);
                                                    $maxVal = $cp?->valor_maximo ?? 100;
                                                @endphp
                                                <div class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                                    <span class="text-slate-200 font-medium text-[17px] truncate mr-2">{{ $det->parametro?->parametro ?? "Parámetro #{$det->id_parametro}" }}</span>
                                                    <span class="font-mono font-bold text-amber-300 shrink-0 text-[17px]">
                                                        {{ number_format($det->valor, 1) }} / {{ number_format($maxVal, 0) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if ($presentacion->inscripcion?->categoria?->estaFinalizada())
                                            <div class="pt-1">
                                                <div class="py-2.5 px-3 rounded-lg bg-slate-950/80 border border-slate-800 text-slate-400 text-sm font-semibold text-center flex items-center justify-center gap-2">
                                                    <span>🔒</span>
                                                    <span>Calificaciones cerradas para esta categoría</span>
                                                </div>
                                            </div>
                                        @else
                                            <div data-contenedor-corregir class="{{ ($presentacion->estado === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO) ? 'pt-2' : 'hidden pt-2' }}">
                                                <a href="{{ route('jurado.calificaciones.corregir.form', $presentacion) }}"
                                                   class="block text-center py-3 px-4 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/40 text-amber-300 font-bold text-[17px] transition">
                                                    ✏️ Corregir calificación
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                @elseif ($presentacion->inscripcion?->categoria?->estaFinalizada())
                                    <!-- Caso: Calificaciones cerradas sin emitir voto -->
                                    <div class="bg-slate-900/90 rounded-2xl border border-slate-800 p-6 text-center space-y-3 shadow-xl">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 mx-auto flex items-center justify-center text-xl">
                                            🔒
                                        </div>
                                        <h3 class="text-sm font-bold text-white">Calificaciones Cerradas</h3>
                                        <p class="text-xs text-slate-400">
                                            La categoría "{{ $presentacion->inscripcion?->categoria?->categoria }}" ha cerrado sus evaluaciones. No se permiten nuevos registros de notas.
                                        </p>
                                    </div>

                                @else
                                    <!-- Caso: Formulario de Calificación Activo (Desfijado, tamaño natural) -->
                                    <div class="bg-slate-900/90 rounded-2xl border border-slate-800 p-4 sm:p-5 shadow-xl space-y-3">
                                        
                                        <!-- Header de Calificación -->
                                        <div class="flex items-center justify-between border-b border-slate-800 pb-2.5 mb-2 shrink-0">
                                            <h3 class="text-[15px] sm:text-[17px] font-bold text-white flex items-center gap-2">
                                                <span class="text-amber-400 text-base">⭐</span>
                                                <span>Calificar Participante</span>
                                            </h3>

                                            <div class="flex items-center gap-2 bg-slate-950 border border-amber-500/40 px-3 py-1 rounded-xl shadow-inner">
                                                <span class="text-[13px] uppercase font-bold text-slate-400 tracking-wider">Total:</span>
                                                <span id="displayTotalTop" class="text-[17px] sm:text-[19px] font-black text-amber-400 font-mono tabular-nums leading-none">0.00</span>
                                                <span class="text-[13px] text-slate-400 font-semibold">/ 100</span>
                                            </div>
                                        </div>

                                        <form id="formCalificacion" method="POST" action="{{ route('jurado.calificaciones.guardar', $presentacion) }}" class="space-y-3">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

                                            <!-- Lista de Parámetros con tamaño natural -->
                                            <div class="space-y-2.5">
                                                @foreach ($categoriaParametros as $cp)
                                                    @php
                                                        $idParam = $cp->id_parametro;
                                                        $nombreParam = $cp->parametro?->parametro ?? "Parámetro #{$idParam}";
                                                        $minVal = (float) $cp->valor_minimo;
                                                        $maxVal = (float) $cp->valor_maximo;
                                                        $valorPrevio = old("valores.{$idParam}", $minVal);
                                                    @endphp

                                                    <div class="bg-slate-950/70 border border-slate-800/90 rounded-xl p-2 transition hover:border-slate-700"
                                                         data-param-container="{{ $idParam }}">
                                                        
                                                        <div class="flex items-center justify-between mb-1.5">
                                                            <span class="font-bold text-[16px] sm:text-[17px] text-slate-100 truncate mr-2">{{ $nombreParam }}</span>
                                                            <div class="flex items-center shrink-0">
                                                                <span class="text-[14px] sm:text-[15px] text-slate-400 font-mono font-semibold">Máx: {{ number_format($maxVal, 0) }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-3">
                                                            <input type="range"
                                                                   id="slider_{{ $idParam }}"
                                                                   min="{{ $minVal }}"
                                                                   max="{{ $maxVal }}"
                                                                   step="0.5"
                                                                   value="{{ $valorPrevio }}"
                                                                   class="festival-slider flex-1"
                                                                   oninput="sincronizarDesdeSlider({{ $idParam }})">

                                                            <input type="number"
                                                                   name="valores[{{ $idParam }}]"
                                                                   id="input_{{ $idParam }}"
                                                                   min="{{ $minVal }}"
                                                                   max="{{ $maxVal }}"
                                                                   step="0.5"
                                                                   value="{{ $valorPrevio }}"
                                                                   required
                                                                   class="w-[66px] h-9 py-1 px-1.5 text-center text-base font-black text-white bg-slate-900 border border-slate-700 rounded-lg focus:ring-1 focus:ring-amber-500 tabular-nums font-mono shrink-0"
                                                                   oninput="sincronizarDesdeInput({{ $idParam }})"
                                                                   onblur="validarLimiteBlur(this)">
                                                        </div>

                                                        @error("valores.{$idParam}")
                                                            <p class="text-[10px] text-rose-400 font-semibold mt-0.5">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="pt-1 shrink-0">
                                                @php
                                                    $puedeCalificarAhora = ($presentacion !== null) && ($presentacion->cronometroCorriendo() || $presentacion->estado === App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO || $presentacion->estado === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO || $presentacion->cronometro_iniciado_at !== null);
                                                @endphp
                                                <button type="button"
                                                        id="btnConfirmarCalificacion"
                                                        onclick="abrirModalConfirmacion()"
                                                        @disabled(! $puedeCalificarAhora)
                                                        class="w-full py-2.5 sm:py-3 px-5 rounded-xl {{ $puedeCalificarAhora ? 'bg-gradient-to-r from-[#5c0618] to-rose-700 hover:from-[#720a20] hover:to-rose-600 text-white cursor-pointer shadow-lg hover:shadow-rose-950/50' : 'bg-slate-800 border border-slate-700 text-slate-400 cursor-not-allowed opacity-60' }} font-bold text-[14px] sm:text-[15px] uppercase tracking-wider transition flex items-center justify-center gap-2">
                                                    <span class="text-base">{{ $puedeCalificarAhora ? '✓' : '⏳' }}</span>
                                                    <span id="btnCalificarTexto">{{ $puedeCalificarAhora ? 'Registrar calificación oficial' : 'Esperando inicio de presentación para calificar' }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            @else
                                {{-- Jurado no asignado --}}
                                <div class="bg-slate-900/80 rounded-2xl border border-slate-800 p-6 text-center space-y-2">
                                    <p class="text-sm font-semibold text-slate-300">Modo Espectador</p>
                                    <p class="text-xs text-slate-500">No está asignado como jurado para esta presentación.</p>
                                </div>
                            @endif

                        @else
                            {{-- Administrador --}}
                            <div class="bg-slate-900/80 rounded-2xl border border-slate-800 p-5 space-y-3 shadow-xl">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Monitoreo de Jurados</h3>
                                    <span class="text-xs text-slate-400">{{ $juradosAsignados->count() }} asignados</span>
                                </div>

                                <div class="space-y-2">
                                    @forelse ($juradosAsignados as $jp)
                                        @php
                                            $calif = $jp->calificacion;
                                            $haCalificado = $calif && $calif->esEmitida();
                                        @endphp
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-950/60 border border-slate-800 text-xs"
                                             data-jurado-usuario-id="{{ $jp->id_usuario }}">
                                            <span class="text-slate-300 font-medium">{{ $jp->usuario?->nombreCompleto() ?? 'Jurado' }}</span>
                                            <span data-jurado-estado-badge class="px-2 py-0.5 rounded text-[10px] font-bold {{ $haCalificado ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                                {{ $haCalificado ? 'Voto emitido' : 'Pendiente' }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500 text-center py-2">Sin jurados asignados.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

            {{-- Escenario Vacío --}}
            <div data-escenario-vacio class="{{ $presentacion === null ? 'flex-1 flex flex-col items-center justify-center text-center px-6 py-12' : 'hidden flex-1 flex flex-col items-center justify-center text-center px-6 py-12' }}">
                <h2 class="text-3xl font-bold text-slate-200">No hay presentación en curso</h2>
                <p class="mt-2 text-xs sm:text-sm text-slate-400">Cuando se llame a una presentación aparecerá aquí.</p>
            </div>

        </main>

        <!-- Barra Inferior: Siguiente Presentación (Estilo Gala Festival) -->
        @if ($siguiente !== null)
            <footer data-siguiente-contenedor class="px-4 py-2.5 border-t border-slate-800/80 bg-slate-950/90 backdrop-blur-md w-full shrink-0">
                <div class="max-w-7xl mx-auto flex items-center flex-wrap gap-2 text-xs">
                    <span class="text-amber-400 font-extrabold tracking-widest text-[11px] uppercase">SIGUIENTE:</span>
                    <span data-siguiente-nombre class="text-amber-200 font-cinzel font-extrabold uppercase tracking-wider text-xs sm:text-sm">{{ $siguiente->nombreMostrado() }}</span>
                    <span class="text-slate-700 font-light mx-1">|</span>
                    <span data-siguiente-categoria class="text-slate-400 font-medium">
                        {{ $siguiente->inscripcion?->categoria?->categoria ?? '—' }}
                        @if ($siguiente->inscripcion?->departamento)
                            · {{ $siguiente->inscripcion->departamento->nombre }}
                        @endif
                    </span>
                </div>
            </footer>
        @endif

        <!-- Modal de Confirmación para Jurados -->
        <div id="modalConfirmacion" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl space-y-4">
                <h3 class="text-base font-black text-white">¿Confirmar calificación?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Está registrando una calificación total de <strong id="modalNotaTotal" class="text-amber-400 font-bold font-mono">0.00</strong> puntos para <strong class="text-white">{{ $presentacion?->nombreMostrado() ?? '' }}</strong>.
                </p>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" onclick="cerrarModalConfirmacion()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg">
                        Cancelar
                    </button>
                    <button type="button" onclick="document.getElementById('formCalificacion').submit()" class="px-4 py-1.5 bg-rose-700 hover:bg-rose-600 text-white text-xs font-bold rounded-lg shadow">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- Notificación Toast: Presentación Finalizada (Flotante inferior derecha descartable) -->
        @php
            $esFinalizado = ($presentacion?->estado === App\Support\Enums\PresentacionEstadoEnum::FINALIZADO || $presentacion?->cronometro_finalizado_at !== null);
        @endphp
        <div data-aviso-finalizado
             class="{{ $esFinalizado ? 'flex' : 'hidden' }} fixed bottom-14 right-4 sm:right-6 z-50 max-w-sm bg-slate-900/95 border-2 border-amber-500/80 rounded-2xl p-4 shadow-2xl backdrop-blur-md items-start gap-3 transition-all duration-300">
            <div class="w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
            </div>
            <div class="flex-1 space-y-1">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-400 font-cinzel">
                        Presentación Finalizada
                    </h4>
                    <button type="button"
                            onclick="this.closest('[data-aviso-finalizado]').classList.add('hidden'); this.closest('[data-aviso-finalizado]').classList.remove('flex');"
                            class="text-slate-400 hover:text-white text-base font-bold px-1.5 py-0.5 leading-none rounded hover:bg-slate-800 transition cursor-pointer"
                            title="Cerrar notificación">
                        ✕
                    </button>
                </div>
                <p class="text-xs text-slate-200 leading-relaxed">
                    La presentación ha finalizado, puedes registrar o modificar tu calificación hasta que el administrador llame al siguiente participante.
                </p>
            </div>
        </div>

        <!-- Notificación cuando se llama al siguiente participante al escenario -->
        <div id="avisoSiguienteParticipante" class="hidden fixed bottom-6 right-6 z-50 max-w-sm bg-slate-900/95 border-2 border-amber-500/80 rounded-2xl p-4 shadow-2xl space-y-2.5 backdrop-blur-sm animate-pulse">
            <div class="flex items-center gap-2 text-amber-300 font-bold text-xs uppercase tracking-wider">
                <span class="text-base">📢</span>
                <span>Nuevo participante en escenario</span>
            </div>
            <p id="avisoSiguienteTexto" class="text-xs text-slate-200 leading-relaxed">
                El siguiente participante ha sido llamado al escenario.
            </p>
            <div class="flex items-center justify-between pt-1 gap-2 border-t border-slate-800/80">
                <span id="avisoSiguienteContador" class="text-[11px] text-amber-400 font-mono font-bold">Cargando en 4s...</span>
                <button type="button" onclick="window.location.reload()" class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black rounded-lg transition shadow">
                    Cargar ahora
                </button>
            </div>
        </div>

        <script>
            function sincronizarDesdeSlider(idParam) {
                const slider = document.getElementById('slider_' + idParam);
                const input = document.getElementById('input_' + idParam);
                const badge = document.getElementById('badge_nota_' + idParam);

                if (slider && input) {
                    const min = parseFloat(slider.min) || 0;
                    const max = parseFloat(slider.max) || 100;
                    let val = parseFloat(slider.value) || 0;
                    if (val > max) val = max;
                    if (val < min) val = min;

                    slider.value = val;
                    input.value = val;
                    if (badge) badge.textContent = val.toFixed(1);
                    recalcularTotal();
                }
            }

            function sincronizarDesdeInput(idParam) {
                const slider = document.getElementById('slider_' + idParam);
                const input = document.getElementById('input_' + idParam);
                const badge = document.getElementById('badge_nota_' + idParam);

                if (slider && input) {
                    const min = parseFloat(input.min) || 0;
                    const max = parseFloat(input.max) || 100;
                    let val = parseFloat(input.value);

                    if (!isNaN(val)) {
                        if (val > max) {
                            val = max;
                            input.value = max;
                        } else if (val < 0) {
                            val = min;
                            input.value = min;
                        }
                        slider.value = Math.max(min, Math.min(max, val));
                        if (badge) badge.textContent = val.toFixed(1);
                    }
                    recalcularTotal();
                }
            }

            function validarLimiteBlur(input) {
                const min = parseFloat(input.min) || 0;
                const max = parseFloat(input.max) || 100;
                let val = parseFloat(input.value);

                if (isNaN(val) || val < min) {
                    input.value = min;
                } else if (val > max) {
                    input.value = max;
                }
                const idParam = input.id.replace('input_', '');
                sincronizarDesdeInput(idParam);
            }

            function recalcularTotal() {
                let total = 0;
                document.querySelectorAll('input[type="number"][name^="valores"]').forEach(inp => {
                    const min = parseFloat(inp.min) || 0;
                    const max = parseFloat(inp.max) || 100;
                    let val = parseFloat(inp.value);
                    if (isNaN(val) || val < min) {
                        val = min;
                    } else if (val > max) {
                        val = max;
                    }
                    total += val;
                });

                total = Math.round(total * 100) / 100;
                const formatted = total.toFixed(2);
                
                const displayTop = document.getElementById('displayTotalTop');
                const displayModal = document.getElementById('modalNotaTotal');
                
                if (displayTop) displayTop.textContent = formatted;
                if (displayModal) displayModal.textContent = formatted;
            }

            function abrirModalConfirmacion() {
                document.querySelectorAll('input[type="number"][name^="valores"]').forEach(inp => {
                    validarLimiteBlur(inp);
                });
                recalcularTotal();
                const modal = document.getElementById('modalConfirmacion');
                if (modal) modal.classList.remove('hidden');
            }

            function cerrarModalConfirmacion() {
                const modal = document.getElementById('modalConfirmacion');
                if (modal) modal.classList.add('hidden');
            }

            document.addEventListener('DOMContentLoaded', () => {
                recalcularTotal();
            });
        </script>
    </body>
</html>