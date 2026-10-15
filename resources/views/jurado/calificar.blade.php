<x-layouts.jurado>
    @php
        $inscripcion = $presentacion->inscripcion;
        $participante = $inscripcion?->participantes->first();
        $nombrePrincipal = $presentacion->nombreMostrado();
        $pseudonimo = $participante?->pseudonimo ?? $presentacion->nombre_presentacion ?? $nombrePrincipal;
        $inicial = mb_substr($nombrePrincipal, 0, 1, 'UTF-8');
        $departamentoNombre = $inscripcion?->departamento?->nombre ?? 'Bolivia';

        $enEscenario = ($presentacion->estado === \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO);
        $participando = ($presentacion->estado === \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO);
        $finalizado = ($presentacion->estado === \App\Support\Enums\PresentacionEstadoEnum::FINALIZADO);
        $esEnVivo = ($enEscenario || $participando);

        $cronometro = app(\App\Modules\Presentaciones\Services\GestionarCronometroService::class)->obtenerEstado($presentacion);
        $corriendo = $cronometro['corriendo'] ?? false;
        $segundosRestantes = $cronometro['segundos_restantes'] ?? (($presentacion->tiempo_maximo ?? 10) * 60);
        $tiempoMaximo = $presentacion->tiempo_maximo ?? 10;

        $puedeControlar = auth()->user()->esAdmin()
            || auth()->user()->puedeControlarTiempo()
            || $presentacion->jurados->where('id_usuario', auth()->id())->where('es_controlador', true)->isNotEmpty();
    @endphp

    <x-slot:header>
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('jurado.inicio') }}"
                   class="px-2.5 py-1 rounded-lg bg-[#14040a] hover:bg-[#250714] text-xs font-bold text-amber-300 hover:text-amber-200 border border-[#6b0015]/60 transition flex items-center gap-1.5 shadow-sm">
                    <span>←</span>
                    <span class="hidden sm:inline">Panel</span>
                </a>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $participando ? 'bg-emerald-400 animate-ping' : ($enEscenario ? 'bg-rose-500 animate-pulse' : 'bg-amber-400') }}"></span>
                    <h2 class="font-cinzel text-base sm:text-lg font-bold text-white tracking-wide truncate max-w-[280px] sm:max-w-md">
                        Evaluación de Participante
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="px-2.5 py-0.5 bg-[#1a050d] text-amber-300 text-[11px] font-bold rounded-full border border-amber-500/40">
                    {{ $inscripcion?->fase?->fase?->label() ?? 'Etapa Final' }}
                </span>
                <span class="px-2.5 py-0.5 bg-[#260a15] text-rose-300 text-[11px] font-bold rounded-full border border-rose-500/40 truncate max-w-[150px] sm:max-w-[220px]"
                      title="{{ $inscripcion?->categoria?->categoria }}">
                    {{ $inscripcion?->categoria?->categoria ?? 'Categoría' }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="max-w-7xl mx-auto space-y-3"
         id="contenedorCalificacionVivo"
         data-presentacion-id="{{ $presentacion->id }}"
         data-cronometro-corriendo="{{ $corriendo ? '1' : '0' }}"
         data-cronometro-iniciado-at="{{ $cronometro['cronometro_iniciado_at'] ?? $presentacion->cronometro_iniciado_at?->toIso8601String() ?? '' }}"
         data-cronometro-finalizado-at="{{ $cronometro['cronometro_finalizado_at'] ?? $presentacion->cronometro_finalizado_at?->toIso8601String() ?? '' }}"
         data-segundos-restantes="{{ $segundosRestantes }}"
         data-tiempo-maximo="{{ $tiempoMaximo }}">

        @if (session('error'))
            <div class="p-3 rounded-xl bg-[#2b080f] border border-rose-500/40 text-rose-200 text-xs flex items-center gap-2 shadow-md animate-fadeIn">
                <span class="text-base font-bold text-rose-400">⚠️</span>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('status') || session('success'))
            <div class="p-3 rounded-xl bg-[#0f2418] border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-2 shadow-md animate-fadeIn">
                <span class="text-base font-bold text-emerald-400">✓</span>
                <p class="font-semibold">{{ session('status') ?? session('success') }}</p>
            </div>
        @endif

        <!-- Contenedor Principal: 2 Columnas Ajustadas al Viewport -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-5 items-start">
            
            <!-- Columna Izquierda: Tarjeta de Resumen + Cronómetro y Controles (4 cols) -->
            <div class="lg:col-span-4 space-y-3">
                <div class="bg-[#120409]/95 rounded-2xl border border-[#3b000b] p-3.5 sm:p-4 text-center space-y-3 shadow-xl">
                    
                    <!-- Header / Foto del Participante o Logo Oficial -->
                    <div class="relative flex items-center justify-center pt-1">
                        @if ($presentacion->fotoSinFondoUrl())
                            <div class="relative group">
                                <div class="absolute -inset-1.5 bg-gradient-to-r from-amber-500/20 via-rose-500/25 to-amber-500/20 rounded-2xl blur-sm opacity-70"></div>
                                <div class="relative h-24 w-24 sm:h-28 sm:w-28 rounded-2xl bg-[radial-gradient(ellipse_at_center,rgba(212,175,55,0.15),rgba(18,4,9,0.95)_75%)] border border-amber-500/30 flex items-center justify-center p-1.5 shadow-xl overflow-hidden">
                                    <img src="{{ $presentacion->fotoSinFondoUrl() }}"
                                         alt="{{ $nombrePrincipal }}"
                                         class="w-full h-full object-contain drop-shadow-[0_4px_10px_rgba(0,0,0,0.85)]">
                                </div>
                            </div>
                        @elseif ($presentacion->fotoUrl())
                            <img src="{{ $presentacion->fotoUrl() }}"
                                 alt="{{ $nombrePrincipal }}"
                                 class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl object-cover object-top border border-[#4a0817] shadow-lg">
                        @else
                            <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl bg-[radial-gradient(ellipse_at_center,rgba(180,83,9,0.25),rgba(5,1,3,0.95)_70%)] border border-[#4a0817] shadow-md flex flex-col items-center justify-center p-2">
                                <img src="/images/logo-oficial.png"
                                     alt="Aquí Canta Bolivia"
                                     class="h-12 w-12 object-contain drop-shadow-[0_0_10px_rgba(212,175,55,0.4)]"
                                     onerror="this.src='/images/logo-oficial.png'">
                                <span class="text-[8px] uppercase tracking-wider text-[#d4af37] font-cinzel font-bold mt-1">Gador 2026</span>
                            </div>
                        @endif
                    </div>

                    <!-- Datos del Participante -->
                    <div class="space-y-0.5">
                        <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#20050e] text-amber-300 text-[10px] font-bold border border-[#4a0817]">
                            <span>♪</span>
                            <span class="truncate max-w-[200px]">{{ $inscripcion?->categoria?->categoria ?? 'Categoría' }}</span>
                        </div>

                        <h2 class="text-base sm:text-lg font-black text-[#fef9c3] font-cinzel tracking-wide leading-tight group-hover:text-amber-300 line-clamp-1"
                            title="{{ $nombrePrincipal }}">
                            {{ $nombrePrincipal }}
                        </h2>

                        @php
                            $departamentoBandera = $inscripcion?->departamento?->bandera_url ?? \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($departamentoNombre);
                        @endphp
                        <div class="flex items-center justify-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-300">
                            @if ($departamentoBandera)
                                <img src="{{ $departamentoBandera }}" alt="{{ $departamentoNombre }}" class="h-3.5 w-auto object-cover rounded-[2px] shadow-sm">
                            @else
                                <span>🇧🇴</span>
                            @endif
                            <span>{{ $departamentoNombre }}</span>
                            @if ($pseudonimo !== $nombrePrincipal)
                                <span class="text-amber-400/80 italic font-sans lowercase">(«{{ $pseudonimo }}»)</span>
                            @endif
                        </div>

                        @if ($presentacion->cancion_tema)
                            <p class="text-[10px] text-amber-300/90 italic truncate max-w-[250px] mx-auto" title="{{ $presentacion->cancion_tema }}">
                                Tema: "{{ $presentacion->cancion_tema }}"
                            </p>
                        @endif
                    </div>

                    <!-- Grid 3 cajas informativas compactas -->
                    <div class="grid grid-cols-3 gap-1.5 text-left pt-1">
                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">N.° Pres.</span>
                            <p class="text-xs font-black text-amber-300">#{{ $presentacion->id }}</p>
                        </div>

                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">Hora</span>
                            <p class="text-xs font-bold text-slate-200">
                                {{ $inscripcion?->hora_presentacion ?? ($presentacion->hora_inicio ? $presentacion->hora_inicio->format('H:i') : 'En curso') }}
                            </p>
                        </div>

                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">Estado</span>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase {{ $participando ? 'bg-emerald-600 text-white animate-pulse' : ($enEscenario ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-300') }}">
                                {{ $participando ? 'En Vivo' : ($enEscenario ? 'Escenario' : $presentacion->estado?->label()) }}
                            </span>
                        </div>
                    </div>

                    <!-- Módulo de Cronómetro Integrado y Controles para Delegado/Presidente -->
                    <div class="bg-black/60 border border-[#3b000b] rounded-xl p-2.5 sm:p-3 shadow-inner space-y-2">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $corriendo ? 'bg-emerald-400 animate-ping' : 'bg-amber-400' }}"></span>
                                <span class="text-[9px] uppercase font-black tracking-widest text-amber-400">Cronómetro</span>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400 font-semibold">
                                Límite: {{ $tiempoMaximo }}:00
                            </span>
                        </div>

                        <div class="text-3xl sm:text-4xl font-black font-mono tracking-tight tabular-nums text-amber-300"
                             id="cronometroDisplayLive">
                            {{ gmdate('i:s', $segundosRestantes) }}
                        </div>

                        <!-- Botones de Acción del Cronómetro (Solo si tiene permisos de control) -->
                        @if ($puedeControlar)
                            <div class="pt-1.5 border-t border-[#2d050f] space-y-1.5">
                                @if ($enEscenario || ! $corriendo)
                                    <form method="POST" action="{{ route('presentaciones.cronometro.iniciar', $presentacion) }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-1.5">
                                            <span>▶</span>
                                            <span>Iniciar Cronómetro</span>
                                        </button>
                                    </form>
                                @elseif ($corriendo || $participando)
                                    <div class="flex items-center gap-1.5">
                                        <form method="POST" action="{{ route('presentaciones.cronometro.detener', $presentacion) }}" class="flex-1">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full py-2 px-2.5 rounded-xl bg-gradient-to-r from-rose-700 to-rose-600 hover:from-rose-600 hover:to-rose-500 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-1">
                                                <span>⏹</span>
                                                <span>Detener</span>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('mesa.presentaciones.transicion', $presentacion) }}" class="flex-1" onsubmit="return confirm('¿Concluir la presentación y pasar a estado Finalizado?')">
                                            @csrf
                                            <input type="hidden" name="estado" value="{{ \App\Support\Enums\PresentacionEstadoEnum::FINALIZADO->value }}">
                                            <button type="submit"
                                                    class="w-full py-2 px-2.5 rounded-xl bg-[#240610] hover:bg-[#38091a] text-slate-200 hover:text-white border border-[#4a0817] font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-1">
                                                <span>✓</span>
                                                <span>Concluir</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>

                </div>
            </div>

            <!-- Columna Derecha: Formulario de Criterios Compacto (8 cols) -->
            <div class="lg:col-span-8">
                <form id="formCalificacion" method="POST" action="{{ route('jurado.calificaciones.guardar', $presentacion) }}">
                    @csrf

                    <div class="bg-[#120409]/95 rounded-2xl border border-[#3b000b] p-3.5 sm:p-5 shadow-xl space-y-3 sm:space-y-4">
                        
                        <!-- Header de Criterios + Nota Total Flotante -->
                        <div class="flex items-center justify-between gap-3 border-b border-[#2d050f] pb-2.5">
                            <div>
                                <h3 class="font-cinzel text-base sm:text-lg font-bold text-[#fef9c3] tracking-wide flex items-center gap-2">
                                    <span>⭐</span>
                                    <span>Criterios de Calificación</span>
                                </h3>
                                <p class="text-[11px] text-slate-400">Deslice el control o escriba la nota de cada parámetro</p>
                            </div>

                            <div class="flex items-center gap-2 bg-black/60 border border-[#4a0817] px-3.5 py-1.5 rounded-xl shadow-inner">
                                <span class="text-xs font-bold text-slate-400 uppercase">Total:</span>
                                <span id="displayTotalTop" class="text-xl sm:text-2xl font-black text-amber-300 tabular-nums font-mono">0.00</span>
                                <span class="text-[10px] text-slate-500 font-bold">/ 100 pts</span>
                            </div>
                        </div>

                        <!-- Lista de Parámetros Compacta -->
                        <div class="space-y-2.5 sm:space-y-3">
                            @foreach ($categoriaParametros as $cp)
                                @php
                                    $idParam = $cp->id_parametro;
                                    $nombreParam = $cp->parametro?->parametro ?? "Parámetro #{$idParam}";
                                    $minVal = (float) $cp->valor_minimo;
                                    $maxVal = (float) $cp->valor_maximo;
                                    $valorPrevio = old("valores.{$idParam}", $minVal);
                                @endphp

                                <div class="bg-black/50 hover:bg-black/70 border border-[#2d050f] hover:border-[#4a0817] rounded-xl p-2.5 sm:p-3 transition space-y-1.5"
                                     data-param-container="{{ $idParam }}">
                                    
                                    <!-- Fila Superior: Nombre del Parámetro + Rango + Nota -->
                                    <div class="flex items-center justify-between gap-2">
                                        <h4 class="font-semibold text-slate-200 text-xs sm:text-sm leading-snug">
                                            {{ $nombreParam }}
                                        </h4>

                                        <div class="flex items-center gap-1.5 shrink-0 font-mono">
                                            <span class="text-[10px] text-slate-400 bg-black/60 px-2 py-0.5 rounded border border-[#2d050f]">
                                                Rango: {{ number_format($minVal, 0) }} - {{ number_format($maxVal, 0) }} pts
                                            </span>
                                            <span class="text-[11px] font-bold text-amber-300 bg-[#2b0813] px-2.5 py-0.5 rounded border border-amber-500/30">
                                                Nota: <span id="badge_nota_{{ $idParam }}">{{ number_format($valorPrevio, 1) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Fila de Controles: Slider + Input Numérico Dark -->
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        <!-- Slider Gradiente -->
                                        <div class="flex-1 space-y-0.5">
                                            <input type="range"
                                                   id="slider_{{ $idParam }}"
                                                   min="{{ $minVal }}"
                                                   max="{{ $maxVal }}"
                                                   step="0.5"
                                                   value="{{ $valorPrevio }}"
                                                   class="festival-slider w-full h-2 cursor-pointer"
                                                   oninput="sincronizarDesdeSlider({{ $idParam }})">
                                            
                                            <div class="flex justify-between text-[9px] text-slate-500 font-bold px-0.5 font-mono">
                                                <span>{{ number_format($minVal, 0) }}</span>
                                                <span id="label_center_{{ $idParam }}" class="text-amber-400 font-black">{{ number_format($valorPrevio, 1) }}</span>
                                                <span>{{ number_format($maxVal, 0) }}</span>
                                            </div>
                                        </div>

                                        <!-- Caja de Texto Numérica con Fondo Oscuro y Texto Blanco/Dorado -->
                                        <div class="shrink-0 w-20 sm:w-24">
                                            <input type="number"
                                                   name="valores[{{ $idParam }}]"
                                                   id="input_{{ $idParam }}"
                                                   min="{{ $minVal }}"
                                                   max="{{ $maxVal }}"
                                                   step="0.5"
                                                   value="{{ $valorPrevio }}"
                                                   required
                                                   style="background-color: #0b0206 !important; color: #ffffff !important;"
                                                   class="w-full py-1.5 px-2 text-center text-sm sm:text-base font-black !text-white !bg-[#0b0206] border border-[#4a0817] focus:border-amber-400 rounded-xl shadow-inner focus:outline-none transition tabular-nums font-mono"
                                                   oninput="sincronizarDesdeInput({{ $idParam }})"
                                                   onblur="validarLimiteBlur(this)">
                                        </div>
                                    </div>
                                    
                                    @error("valores.{$idParam}")
                                        <p class="text-[11px] text-rose-400 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <!-- Botón Principal de Envío -->
                        <div class="pt-2 border-t border-[#2d050f]">
                            <button type="button"
                                    id="btnConfirmarCalificacion"
                                    onclick="abrirModalConfirmacion()"
                                    class="w-full py-3 sm:py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg transition-all duration-200 flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                                <span>✓</span>
                                <span>Registrar Calificación (<span id="btnTotalText" class="font-mono tabular-nums">0.00</span> pts)</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación Oficial (Estilo Gala Oruro) -->
    <div id="modalConfirmacion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-md hidden p-4">
        <div class="bg-gradient-to-b from-[#18050e] via-[#0d0208] to-[#050103] rounded-2xl p-5 sm:p-6 max-w-md w-full mx-auto shadow-2xl border-2 border-amber-400/80 space-y-4 relative">
            <div class="corner-bracket-tl"></div>
            <div class="corner-bracket-tr"></div>
            <div class="corner-bracket-bl"></div>
            <div class="corner-bracket-br"></div>

            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-[#2a0611] text-amber-300 border border-amber-500/50 flex items-center justify-center font-black text-xl shrink-0">
                    ⭐
                </div>
                <div>
                    <h3 class="text-base font-black text-[#fef9c3] font-cinzel">¿Confirmar emisión de voto?</h3>
                    <p class="text-[11px] text-amber-400/80">XXXIII Festival Aquí Canta Bolivia · Oruro 2026</p>
                </div>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Está registrando una calificación total de <strong id="modalNotaTotal" class="text-amber-300 font-black text-base tabular-nums font-mono">0.00</strong> puntos para <strong class="text-white font-bold">{{ $nombrePrincipal }}</strong>.
            </p>

            <div class="p-2.5 bg-black/60 rounded-xl text-[11px] text-slate-400 border border-[#3b000b]">
                Al confirmar, su voto quedará asentado oficialmente en el sistema para el cálculo del ranking.
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button"
                        onclick="cerrarModalConfirmacion()"
                        class="px-4 py-2 bg-[#1a050d] hover:bg-[#2e0917] text-slate-300 text-xs font-bold rounded-xl border border-[#6b0015] transition">
                    Revisar notas
                </button>
                <button type="button"
                        onclick="document.getElementById('formCalificacion').submit()"
                        class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-black rounded-xl shadow-lg transition">
                    Sí, registrar calificación
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function sincronizarDesdeSlider(idParam) {
            const slider = document.getElementById('slider_' + idParam);
            const input = document.getElementById('input_' + idParam);
            const badge = document.getElementById('badge_nota_' + idParam);
            const centerLabel = document.getElementById('label_center_' + idParam);

            if (slider && input) {
                const min = parseFloat(slider.min) || 0;
                const max = parseFloat(slider.max) || 100;
                let val = parseFloat(slider.value) || 0;
                if (val > max) val = max;
                if (val < min) val = min;

                slider.value = val;
                input.value = val;
                if (badge) badge.textContent = val.toFixed(1);
                if (centerLabel) centerLabel.textContent = val.toFixed(1);
                recalcularTotal();
            }
        }

        function sincronizarDesdeInput(idParam) {
            const slider = document.getElementById('slider_' + idParam);
            const input = document.getElementById('input_' + idParam);
            const badge = document.getElementById('badge_nota_' + idParam);
            const centerLabel = document.getElementById('label_center_' + idParam);

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
                    if (centerLabel) centerLabel.textContent = val.toFixed(1);
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
            const btnText = document.getElementById('btnTotalText');
            
            if (displayTop) displayTop.textContent = formatted;
            if (displayModal) displayModal.textContent = formatted;
            if (btnText) btnText.textContent = formatted;
        }

        function abrirModalConfirmacion() {
            // Asegurar que todos los inputs estén dentro de rango antes de abrir modal
            document.querySelectorAll('input[type="number"][name^="valores"]').forEach(inp => {
                validarLimiteBlur(inp);
            });
            recalcularTotal();
            document.getElementById('modalConfirmacion').classList.remove('hidden');
        }

        function cerrarModalConfirmacion() {
            document.getElementById('modalConfirmacion').classList.add('hidden');
        }

        // =========================================================================
        // Temporizador de Cronómetro en Vivo y Sincronización WebSocket (Calificar)
        // =========================================================================
        (function () {
            const container = document.getElementById('contenedorCalificacionVivo');
            const display = document.getElementById('cronometroDisplayLive');
            const dot = document.getElementById('cronometroDot');
            if (!container || !display) return;

            const presId = parseInt(container.dataset.presentacionId, 10);
            const tiempoMaximoMin = parseInt(container.dataset.tiempoMaximo, 10) || 10;
            const tiempoMaximoSeg = tiempoMaximoMin * 60;
            
            let timerInterval = null;

            function formatear(seg) {
                seg = Math.max(0, Math.floor(seg));
                const m = Math.floor(seg / 60).toString().padStart(2, '0');
                const s = (seg % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            }

            function iniciarCountdown(iniciadoAtStr) {
                detenerCountdown();
                if (!iniciadoAtStr) return;

                const startTime = new Date(iniciadoAtStr).getTime();
                if (dot) {
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
                }

                function tick() {
                    const now = Date.now();
                    const transcurrido = Math.floor((now - startTime) / 1000);
                    const restante = Math.max(0, tiempoMaximoSeg - transcurrido);
                    display.textContent = formatear(restante);
                    if (restante <= 0) {
                        detenerCountdown(0);
                    }
                }

                tick();
                timerInterval = setInterval(tick, 1000);
            }

            function detenerCountdown(segundosRestantes) {
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                if (dot) {
                    dot.className = 'w-2 h-2 rounded-full bg-amber-400';
                }
                if (typeof segundosRestantes === 'number') {
                    display.textContent = formatear(segundosRestantes);
                }
            }

            // Estado inicial desde los atributos dataset
            const corriendoInicial = container.dataset.cronometroCorriendo === '1';
            const iniciadoAtInicial = container.dataset.cronometroIniciadoAt;
            const segundosInicial = parseInt(container.dataset.segundosRestantes, 10);

            if (corriendoInicial && iniciadoAtInicial) {
                iniciarCountdown(iniciadoAtInicial);
            } else {
                detenerCountdown(segundosInicial);
            }

            // =====================================================================
            // Sincronización WebSocket (Laravel Echo / Reverb)
            // =====================================================================
            function suscribirEcho() {
                if (!window.Echo) return false;

                try {
                    window.Echo.channel('presentaciones')
                        .listen('.cronometro.actualizado', function (datos) {
                            if (parseInt(datos.presentacion_id, 10) !== presId) return;

                            if (datos.corriendo && datos.cronometro_iniciado_at) {
                                iniciarCountdown(datos.cronometro_iniciado_at);
                            } else {
                                detenerCountdown(datos.segundos_restantes);
                            }
                        })
                        .listen('.presentacion.estado.cambiado', function (datos) {
                            if (parseInt(datos.presentacion_id, 10) !== presId) return;
                            // Si la presentación fue finalizada o el cronómetro se actualizó
                            if (datos.corriendo && datos.cronometro_iniciado_at) {
                                iniciarCountdown(datos.cronometro_iniciado_at);
                            } else if (!datos.corriendo && datos.segundos_restantes !== undefined) {
                                detenerCountdown(datos.segundos_restantes);
                            }
                        });

                    return true;
                } catch (e) {
                    console.warn('Echo listener calificar error:', e);
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

        document.addEventListener('DOMContentLoaded', () => {
            recalcularTotal();
        });
    </script>
    @endpush
</x-layouts.jurado>
