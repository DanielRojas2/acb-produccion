<x-layouts.jurado>
    @php
        $inscripcion = $presentacion->inscripcion;
        $participante = $inscripcion?->participantes->first();
        $nombrePrincipal = $presentacion->nombreMostrado();
        $pseudonimo = $participante?->pseudonimo ?? $presentacion->nombre_presentacion ?? $nombrePrincipal;
        $inicial = mb_substr($nombrePrincipal, 0, 1, 'UTF-8');
        $departamentoNombre = $inscripcion?->departamento?->nombre ?? 'Bolivia';
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
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <h2 class="font-cinzel text-base sm:text-lg font-bold text-white tracking-wide truncate max-w-[280px] sm:max-w-md">
                        Corrección Auditada
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="px-2.5 py-0.5 bg-[#1a050d] text-amber-300 text-[11px] font-bold rounded-full border border-amber-500/40">
                    Modo Corrección
                </span>
                <span class="px-2.5 py-0.5 bg-[#260a15] text-rose-300 text-[11px] font-bold rounded-full border border-rose-500/40 truncate max-w-[150px] sm:max-w-[220px]"
                      title="{{ $inscripcion?->categoria?->categoria }}">
                    {{ $inscripcion?->categoria?->categoria ?? 'Categoría' }}
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="max-w-7xl mx-auto space-y-3">

        <!-- Banner de advertencia de auditoría -->
        <div class="p-2.5 bg-[#1a0d03] border border-amber-500/50 rounded-xl text-amber-200 text-xs flex items-center gap-2.5 shadow-md">
            <span class="text-base shrink-0">⚠️</span>
            <p class="text-[11px] leading-tight text-amber-100/90">
                <strong>Registro de Auditoría Forense:</strong> Toda modificación asentará un snapshot histórico con fecha, usuario y valores previos para garantizar la fe notarial del festival.
            </p>
        </div>

        @if (session('error'))
            <div class="p-3 rounded-xl bg-[#2b080f] border border-rose-500/40 text-rose-200 text-xs flex items-center gap-2 shadow-md animate-fadeIn">
                <span class="text-base font-bold text-rose-400">⚠️</span>
                <p class="font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Contenedor Principal: 2 Columnas -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-5 items-start">
            
            <!-- Columna Izquierda: Tarjeta Resumen del Participante (4 cols) -->
            <div class="lg:col-span-4 space-y-3">
                <div class="bg-[#120409]/95 rounded-2xl border border-[#3b000b] p-3.5 sm:p-4 text-center space-y-3 shadow-xl">
                    
                    <!-- Header / Foto o Logo -->
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

                    <!-- Nombre del participante -->
                    <div class="space-y-0.5">
                        <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#20050e] text-amber-300 text-[10px] font-bold border border-[#4a0817]">
                            <span>♪</span>
                            <span class="truncate max-w-[200px]">{{ $inscripcion?->categoria?->categoria ?? 'Categoría' }}</span>
                        </div>

                        <h2 class="text-base sm:text-lg font-black text-[#fef9c3] font-cinzel tracking-wide leading-tight line-clamp-1"
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
                    </div>

                    <!-- Grid 3 cajas informativas -->
                    <div class="grid grid-cols-3 gap-1.5 text-left pt-1">
                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">N.° Pres.</span>
                            <p class="text-xs font-black text-amber-300">#{{ $presentacion->id }}</p>
                        </div>

                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">Hora</span>
                            <p class="text-xs font-bold text-slate-200">
                                {{ $inscripcion?->hora_presentacion ?? ($presentacion->hora_inicio ? $presentacion->hora_inicio->format('H:i') : 'Finalizado') }}
                            </p>
                        </div>

                        <div class="bg-black/50 border border-[#2d050f] rounded-xl p-1.5 text-center">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block">Estado</span>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-500/40">
                                Finalizado
                            </span>
                        </div>
                    </div>

                    <!-- Nota Previa Emitida -->
                    <div class="bg-black/60 border border-[#3b000b] rounded-xl p-2.5 sm:p-3 text-center space-y-0.5 shadow-inner">
                        <span class="text-[9px] uppercase font-black tracking-widest text-amber-400 block">Nota Previa Emitida</span>
                        <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white tabular-nums">
                            {{ number_format($calificacion->nota_total, 2) }}
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">/ 100 puntos</span>
                    </div>

                </div>
            </div>

            <!-- Columna Derecha: Formulario de Corrección de Criterios (8 cols) -->
            <div class="lg:col-span-8">
                <form id="formCorreccion" method="POST" action="{{ route('jurado.calificaciones.corregir', $calificacion) }}">
                    @csrf

                    <div class="bg-[#120409]/95 rounded-2xl border border-[#3b000b] p-3.5 sm:p-5 shadow-xl space-y-3 sm:space-y-4">
                        
                        <!-- Header del Card Criterios -->
                        <div class="flex items-center justify-between gap-3 border-b border-[#2d050f] pb-2.5">
                            <div>
                                <h3 class="font-cinzel text-base sm:text-lg font-bold text-[#fef9c3] tracking-wide flex items-center gap-2">
                                    <span>✏️</span>
                                    <span>Criterios de Calificación (Ajuste)</span>
                                </h3>
                                <p class="text-[11px] text-slate-400">Modifique los valores correspondientes</p>
                            </div>

                            <div class="flex items-center gap-2.5 bg-black/60 border border-[#4a0817] px-3.5 py-1.5 rounded-xl shadow-inner">
                                <span class="text-xs font-bold text-slate-400 uppercase">Nueva Nota:</span>
                                <span id="displayTotalTop" class="text-xl sm:text-2xl font-black text-amber-300 tabular-nums font-mono">0.00</span>
                                <span id="displayDiffTop" class="text-[10px] font-black px-2 py-0.5 rounded font-mono bg-slate-800 text-slate-300">=</span>
                            </div>
                        </div>

                        <!-- Lista de Parámetros -->
                        <div class="space-y-2.5 sm:space-y-3">
                            @foreach ($categoriaParametros as $cp)
                                @php
                                    $idParam = $cp->id_parametro;
                                    $nombreParam = $cp->parametro?->parametro ?? "Parámetro #{$idParam}";
                                    $minVal = (float) $cp->valor_minimo;
                                    $maxVal = (float) $cp->valor_maximo;
                                    $valorPrevio = (float) old("valores.{$idParam}", $detallesActuales->get($idParam)?->valor ?? $minVal);
                                @endphp

                                <div class="bg-black/50 hover:bg-black/70 border border-[#2d050f] hover:border-[#4a0817] rounded-xl p-2.5 sm:p-3 transition space-y-1.5"
                                     data-param-container="{{ $idParam }}">
                                    
                                    <!-- Fila Superior: Nombre + Rango + Nota -->
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

                        <!-- Motivo / Justificación Obligatoria -->
                        <div class="pt-2 border-t border-[#3b000b] space-y-1.5">
                            <label for="motivo" class="block text-[11px] font-bold text-amber-300 uppercase tracking-wider">
                                Motivo o Justificación del ajuste <span class="text-rose-400 font-bold">*</span>
                            </label>
                            <textarea name="motivo"
                                      id="motivo"
                                      rows="2"
                                      required
                                      placeholder="Indique obligatoriamente el motivo del ajuste (ej: Rectificación tras deliberación oficial de mesa)..."
                                      class="w-full px-3 py-2 text-xs text-slate-100 bg-black/80 border border-[#6b0015] focus:border-amber-400 rounded-xl transition placeholder:text-slate-600 focus:outline-none">{{ old('motivo') }}</textarea>
                            @error('motivo')
                                <p class="text-[11px] text-rose-400 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botón de Guardar Corrección -->
                        <div class="pt-1">
                            <button type="submit"
                                    class="w-full py-3 sm:py-3.5 px-6 rounded-xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider shadow-[0_0_20px_rgba(245,158,11,0.35)] transition-all duration-200 flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                                <span>✏️</span>
                                <span>Guardar Corrección Auditada</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const notaAnterior = {{ (float) $calificacion->nota_total }};

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
            const displayDiff = document.getElementById('displayDiffTop');
            
            if (displayTop) displayTop.textContent = formatted;

            if (displayDiff) {
                const diff = Math.round((total - notaAnterior) * 100) / 100;
                if (diff > 0) {
                    displayDiff.textContent = '+' + diff.toFixed(2);
                    displayDiff.className = 'text-[10px] font-black px-2 py-0.5 rounded font-mono bg-emerald-950 text-emerald-300 border border-emerald-500/50';
                } else if (diff < 0) {
                    displayDiff.textContent = diff.toFixed(2);
                    displayDiff.className = 'text-[10px] font-black px-2 py-0.5 rounded font-mono bg-rose-950 text-rose-300 border border-rose-500/50';
                } else {
                    displayDiff.textContent = '=';
                    displayDiff.className = 'text-[10px] font-black px-2 py-0.5 rounded font-mono bg-slate-800 text-slate-300';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            recalcularTotal();
        });
    </script>
    @endpush
</x-layouts.jurado>

