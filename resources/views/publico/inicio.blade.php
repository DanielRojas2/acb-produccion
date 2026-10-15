<x-layouts.publico>
    
    @php
        $catId = $categoriaSeleccionada?->id;
    @endphp

    <div id="rankingLiveWrapper" class="space-y-6">

        <!-- ============================================================== -->
        <!-- TEXTO DE CATEGORÍA A LA DERECHA Y HR DIVIDER DORADO            -->
        <!-- ============================================================== -->
        <div class="w-full pt-1 pb-4 select-none">
            <!-- Texto de la Categoría alineado al lado derecho -->
            <div class="flex justify-end items-center px-1 mb-2">
                <h2 class="text-base sm:text-lg md:text-xl lg:text-2xl font-black uppercase tracking-[0.25em] font-cinzel gold-gradient-text" style="color: #d4af37; text-shadow: 0 0 15px rgba(212,175,55,0.6);">
                    {{ $categoriaSeleccionada ? $categoriaSeleccionada->categoria : 'Etapa Final Oruro 2026' }}
                </h2>
            </div>

            <!-- HR Dorado Estilizado de Gala -->
            <hr class="w-full" style="height: 2.5px; border: none; background: linear-gradient(90deg, rgba(107,0,21,0.1) 0%, rgba(212,175,55,0.7) 20%, #d4af37 100%); box-shadow: 0 0 12px rgba(212, 175, 55, 0.85); margin: 0; opacity: 1;">
        </div>

        <style>
            .podio-card-flip-container {
                perspective: 1200px;
            }
            .podio-card-inner {
                position: relative;
                width: 100%;
                transform-style: preserve-3d;
                -webkit-transform-style: preserve-3d;
                transition: transform 0.9s cubic-bezier(0.34, 1.56, 0.64, 1);
            }
            .podio-card-inner.is-flipped {
                transform: rotateY(180deg);
            }
            .podio-card-back,
            .podio-card-front {
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
            }
            .podio-card-front {
                transform: rotateY(180deg);
            }
        </style>

        <!-- ============================================================== -->
        <!-- MODO 1: PODIO ESTILO F1 (CUANDO LA CATEGORÍA ESTÁ REVELADA)    -->
        <!-- ============================================================== -->
        @if ($esPodio && $ranking->isNotEmpty())
            
            <div class="flex flex-col lg:flex-row gap-6 items-start w-full">
                
                <!-- Columna Izquierda/Centro: Podio de 3 Escalones (Estilo F1) -->
                <div id="contenedorPodioEscalones" class="w-full flex-1 transition-all duration-700 bg-gradient-to-b from-[#14040a] via-[#090205] to-[#050103] rounded-3xl border border-[#6b0015]/60 p-5 sm:p-8 shadow-2xl relative overflow-hidden">
                    
                    <div class="corner-bracket-tl"></div>
                    <div class="corner-bracket-tr"></div>
                    <div class="corner-bracket-bl"></div>
                    <div class="corner-bracket-br"></div>

                    <!-- Título de Ganadores Oficial -->
                    <div class="text-center mb-6 border-b border-[#3b000b] pb-4">
                        <h2 class="text-xl sm:text-2xl font-black gold-gradient-text font-cinzel tracking-wider uppercase">
                            @if ($categoriaSeleccionada?->tieneUnSoloGanador())
                                {{ $categoriaSeleccionada->esNusta() ? 'Elección y Proclamación de la Ñusta' : 'Ganador Oficial de la Categoría' }}
                            @else
                                Ganadores de la Categoría
                            @endif
                        </h2>
                    </div>

                    @if ($categoriaSeleccionada?->tieneUnSoloGanador())
                        <!-- ================================================= -->
                        <!-- MODO GANADOR ÚNICO (ÑUSTA / ESPECIAL: 1 GANADOR) -->
                        <!-- ================================================= -->
                        <div class="flex flex-col items-center justify-center pt-4 pb-2 max-w-lg mx-auto w-full">
                            @if ($primerLugar)
                                <div class="podio-card-flip-container w-full max-w-[340px]">
                                    <div id="podioCard_1" class="podio-card-inner {{ $lugar1Revelado ? 'is-flipped' : '' }}">
                                        
                                        <!-- CARA TRASERA: MISTERIO CON SIGNO '?' -->
                                        <div class="podio-card-back w-full bg-gradient-to-b from-[#2a0618] via-[#14030c] to-[#070104] rounded-2xl border-2 border-[#d4af37] shadow-[0_0_35px_rgba(212,175,55,0.3)] overflow-hidden flex flex-col items-center justify-between p-5 min-h-[430px] select-none">
                                            <div class="w-full bg-gradient-to-r from-[#b45309] via-[#d4af37] to-[#b45309] py-2.5 px-4 flex items-center justify-between text-slate-950 rounded-xl">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-2xl">👑</span>
                                                    <span class="text-xl font-black font-cinzel tracking-wider">★</span>
                                                </div>
                                                <span class="text-xs font-black uppercase tracking-[0.2em] font-cinzel">
                                                    {{ $categoriaSeleccionada->etiquetaGanador() }}
                                                </span>
                                            </div>

                                            <div class="my-auto flex flex-col items-center justify-center py-6">
                                                <div class="relative flex items-center justify-center mb-4">
                                                    <div class="absolute inset-0 rounded-full bg-amber-400/25 blur-2xl animate-pulse"></div>
                                                    <svg class="w-28 h-28 text-amber-400 drop-shadow-[0_0_25px_rgba(212,175,55,0.7)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10" class="stroke-amber-600/80 fill-slate-950/90"></circle>
                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="2.5"></line>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-black uppercase tracking-[0.3em] gold-gradient-text font-cinzel animate-pulse text-center">
                                                    ★ Ganador Único ★
                                                </span>
                                                <span class="text-[10px] text-amber-300/70 font-cinzel mt-1 uppercase tracking-widest">
                                                    En Espera de Revelación
                                                </span>
                                            </div>

                                            <div class="w-full text-center py-2 border-t border-[#800020]/80">
                                                <span class="text-[9px] uppercase tracking-widest text-amber-400/70 font-mono">XXXIII Festival Aquí Canta Bolivia</span>
                                            </div>
                                        </div>

                                        <!-- CARA DELANTERA: GANADOR REVELADO -->
                                        <div class="podio-card-front absolute inset-0 w-full bg-gradient-to-b from-[#2e091a] via-[#17040e] to-[#090105] rounded-2xl border-2 border-[#d4af37] shadow-[0_0_35px_rgba(212,175,55,0.3)] overflow-hidden cursor-pointer flex flex-col justify-between"
                                             data-card-id="{{ $primerLugar['id'] }}"
                                             onclick="abrirModalDesglose({{ $primerLugar['id'] }})">
                                            
                                            <!-- Corona / Insignia -->
                                            <div class="bg-gradient-to-r from-[#b45309] via-[#d4af37] to-[#b45309] py-2.5 px-4 flex items-center justify-between text-slate-950">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-2xl">👑</span>
                                                    <span class="text-lg font-black font-cinzel tracking-wider">1°</span>
                                                </div>
                                                <span class="text-xs font-black uppercase tracking-[0.2em] font-cinzel">
                                                    {{ $categoriaSeleccionada->etiquetaGanador() }}
                                                </span>
                                            </div>

                                            <!-- Imagen del Participante -->
                                            <div class="relative w-full aspect-[4/4] bg-[#050103] overflow-hidden">
                                                @if ($primerLugar['foto_url'])
                                                    <img src="{{ $primerLugar['foto_url'] }}" alt="{{ $primerLugar['nombre'] }}" class="w-full h-full object-cover object-top card-foto">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(212,175,55,0.4),rgba(5,1,3,0.95)_70%)] p-4">
                                                        <img src="/images/logo-oficial.png" alt="ACB" class="h-28 w-28 object-contain opacity-90 drop-shadow-[0_0_20px_rgba(212,175,55,0.6)]">
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-[#090105] via-transparent to-transparent pointer-events-none"></div>
                                            </div>

                                            <!-- Nombre y Puntaje Final -->
                                            <div class="p-4 text-center space-y-2 bg-[#090105]">
                                                <h3 class="text-lg sm:text-xl font-black text-[#fef9c3] font-cinzel uppercase tracking-wide line-clamp-2 min-h-[3rem] flex items-center justify-center card-nombre" title="{{ $primerLugar['nombre'] }}">
                                                    {{ $primerLugar['nombre'] }}
                                                </h3>

                                                @if (!empty($primerLugar['departamento']))
                                                    @php
                                                        $bandera1 = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($primerLugar['departamento']);
                                                    @endphp
                                                    <p class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center justify-center gap-1.5">
                                                        @if ($bandera1)
                                                            <img src="{{ $bandera1 }}" alt="{{ $primerLugar['departamento'] }}" class="h-4 w-auto object-cover rounded-[2px] shadow-sm">
                                                        @endif
                                                        <span>{{ $primerLugar['departamento'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="pt-2 border-t border-[#800020]/80 flex items-center justify-center">
                                                    <span class="text-xl sm:text-2xl font-black gold-gradient-text font-mono tabular-nums bg-[#1f0511] border border-[#d4af37] px-5 py-1.5 rounded-xl shadow-lg card-score">
                                                        {{ number_format($primerLugar['nota_final'], 2) }} <span class="text-xs text-amber-300">pts</span>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Estructura de Podio: 2° Lugar (Izquierda), 1° Lugar (Centro), 3° Lugar (Derecha) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 items-end justify-center pt-4 max-w-4xl mx-auto w-full">
                        
                        <!-- ========================= -->
                        <!-- 2° LUGAR (ESCALÓN PLATA)  -->
                        <!-- ========================= -->
                        @if ($segundoLugar)
                            <div class="order-2 sm:order-1 flex flex-col items-center w-full">
                                <div class="podio-card-flip-container w-full max-w-[270px]">
                                    <div id="podioCard_2" class="podio-card-inner {{ $lugar2Revelado ? 'is-flipped' : '' }}">
                                        
                                        <!-- CARA TRASERA: MISTERIO CON SIGNO '?' -->
                                        <div class="podio-card-back w-full bg-gradient-to-b from-[#180510] via-[#0d0208] to-[#050103] rounded-2xl border-2 border-slate-500/50 shadow-2xl overflow-hidden flex flex-col items-center justify-between p-4 min-h-[380px] select-none">
                                            <div class="w-full bg-gradient-to-r from-slate-700 to-slate-900 border-b border-slate-500/40 py-2 px-3 flex items-center justify-between rounded-xl">
                                                <span class="text-2xl font-black font-cinzel text-slate-200 tracking-wider">2</span>
                                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-300 font-cinzel">Segundo Lugar</span>
                                            </div>

                                            <div class="my-auto flex flex-col items-center justify-center py-6">
                                                <div class="relative flex items-center justify-center mb-3">
                                                    <div class="absolute inset-0 rounded-full bg-slate-400/10 blur-xl animate-pulse"></div>
                                                    <svg class="w-20 h-20 text-slate-400 drop-shadow-[0_0_15px_rgba(148,163,184,0.3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10" class="stroke-slate-600 fill-slate-950/80"></circle>
                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="2.5"></line>
                                                    </svg>
                                                </div>
                                                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-slate-400 font-cinzel animate-pulse">
                                                    En Espera
                                                </span>
                                            </div>

                                            <div class="w-full text-center py-1.5 border-t border-slate-800/80">
                                                <span class="text-[8px] uppercase tracking-widest text-slate-500 font-mono">XXXIII Aquí Canta Bolivia</span>
                                            </div>
                                        </div>

                                        <!-- CARA DELANTERA: GANADOR REVELADO -->
                                        <div class="podio-card-front absolute inset-0 w-full bg-gradient-to-b from-[#1e0813] via-[#10030a] to-[#070104] rounded-2xl border-2 border-slate-400/50 shadow-2xl overflow-hidden cursor-pointer flex flex-col justify-between"
                                             data-card-id="{{ $segundoLugar['id'] }}"
                                             onclick="abrirModalDesglose({{ $segundoLugar['id'] }})">
                                            
                                            <!-- Insignia Número 2 -->
                                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 border-b border-slate-500/40 py-2 px-3 flex items-center justify-between">
                                                <span class="text-2xl font-black font-cinzel text-slate-200 tracking-wider">2</span>
                                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-300 font-cinzel">Segundo Lugar</span>
                                            </div>

                                            <!-- Imagen del Participante -->
                                            <div class="relative w-full aspect-[4/4] bg-[#050103] overflow-hidden">
                                                @if ($segundoLugar['foto_url'])
                                                    <img src="{{ $segundoLugar['foto_url'] }}" alt="{{ $segundoLugar['nombre'] }}" class="w-full h-full object-cover object-top card-foto">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(100,116,139,0.3),rgba(5,1,3,0.95)_70%)] p-4">
                                                        <img src="/images/logo-oficial.png" alt="ACB" class="h-20 w-20 object-contain opacity-80">
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-[#070104] via-transparent to-transparent pointer-events-none"></div>
                                            </div>

                                            <!-- Nombre y Puntaje Final -->
                                            <div class="p-4 text-center space-y-2 bg-[#070104]">
                                                <h3 class="text-sm sm:text-base font-bold text-white font-cinzel uppercase tracking-wide line-clamp-2 min-h-[2.5rem] flex items-center justify-center card-nombre" title="{{ $segundoLugar['nombre'] }}">
                                                    {{ $segundoLugar['nombre'] }}
                                                </h3>

                                                @if (!empty($segundoLugar['departamento']))
                                                    @php
                                                        $bandera2 = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($segundoLugar['departamento']);
                                                    @endphp
                                                    <p class="text-[11px] font-semibold text-slate-300 uppercase tracking-wider flex items-center justify-center gap-1.5">
                                                        @if ($bandera2)
                                                            <img src="{{ $bandera2 }}" alt="{{ $segundoLugar['departamento'] }}" class="h-3.5 w-auto object-cover rounded-[2px] shadow-sm">
                                                        @endif
                                                        <span>{{ $segundoLugar['departamento'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-center">
                                                    <span class="text-base sm:text-lg font-black text-slate-200 font-mono tabular-nums bg-slate-900/90 border border-slate-700 px-3 py-1 rounded-xl shadow-inner card-score">
                                                        {{ number_format($segundoLugar['nota_final'], 2) }} <span class="text-[10px] text-slate-400">pts</span>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="order-2 sm:order-1 hidden sm:block"></div>
                        @endif

                        <!-- ========================= -->
                        <!-- 1° LUGAR (ESCALÓN ORO)    -->
                        <!-- ========================= -->
                        @if ($primerLugar)
                            <div class="order-1 sm:order-2 flex flex-col items-center sm:-translate-y-4 w-full">
                                <div class="podio-card-flip-container w-full max-w-[290px]">
                                    <div id="podioCard_1" class="podio-card-inner {{ $lugar1Revelado ? 'is-flipped' : '' }}">
                                        
                                        <!-- CARA TRASERA: MISTERIO CON SIGNO '?' -->
                                        <div class="podio-card-back w-full bg-gradient-to-b from-[#2a0618] via-[#14030c] to-[#070104] rounded-2xl border-2 border-[#d4af37] shadow-[0_0_30px_rgba(212,175,55,0.25)] overflow-hidden flex flex-col items-center justify-between p-4 min-h-[410px] select-none">
                                            <div class="w-full bg-gradient-to-r from-[#b45309] via-[#d4af37] to-[#b45309] py-2.5 px-3 flex items-center justify-between text-slate-950 rounded-xl">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xl">👑</span>
                                                    <span class="text-2xl font-black font-cinzel tracking-wider">1</span>
                                                </div>
                                                <span class="text-[11px] font-black uppercase tracking-[0.2em] font-cinzel">Primer Lugar</span>
                                            </div>

                                            <div class="my-auto flex flex-col items-center justify-center py-6">
                                                <div class="relative flex items-center justify-center mb-3">
                                                    <div class="absolute inset-0 rounded-full bg-amber-400/20 blur-xl animate-pulse"></div>
                                                    <svg class="w-24 h-24 text-amber-400 drop-shadow-[0_0_20px_rgba(212,175,55,0.6)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10" class="stroke-amber-600/80 fill-slate-950/90"></circle>
                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="2.5"></line>
                                                    </svg>
                                                </div>
                                                <span class="text-xs font-black uppercase tracking-[0.3em] gold-gradient-text font-cinzel animate-pulse">
                                                    ★ Gran Ganador ★
                                                </span>
                                            </div>

                                            <div class="w-full text-center py-1.5 border-t border-[#800020]/80">
                                                <span class="text-[8px] uppercase tracking-widest text-amber-400/70 font-mono">XXXIII Aquí Canta Bolivia</span>
                                            </div>
                                        </div>

                                        <!-- CARA DELANTERA: GANADOR REVELADO -->
                                        <div class="podio-card-front absolute inset-0 w-full bg-gradient-to-b from-[#2e091a] via-[#17040e] to-[#090105] rounded-2xl border-2 border-[#d4af37] shadow-[0_0_30px_rgba(212,175,55,0.25)] overflow-hidden cursor-pointer flex flex-col justify-between"
                                             data-card-id="{{ $primerLugar['id'] }}"
                                             onclick="abrirModalDesglose({{ $primerLugar['id'] }})">
                                            
                                            <!-- Corona / Insignia Número 1 -->
                                            <div class="bg-gradient-to-r from-[#b45309] via-[#d4af37] to-[#b45309] py-2.5 px-3 flex items-center justify-between text-slate-950">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xl">👑</span>
                                                    <span class="text-2xl font-black font-cinzel tracking-wider">1</span>
                                                </div>
                                                <span class="text-[11px] font-black uppercase tracking-[0.2em] font-cinzel">Primer Lugar</span>
                                            </div>

                                            <!-- Imagen del Participante -->
                                            <div class="relative w-full aspect-[4/4] bg-[#050103] overflow-hidden">
                                                @if ($primerLugar['foto_url'])
                                                    <img src="{{ $primerLugar['foto_url'] }}" alt="{{ $primerLugar['nombre'] }}" class="w-full h-full object-cover object-top card-foto">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(212,175,55,0.4),rgba(5,1,3,0.95)_70%)] p-4">
                                                        <img src="/images/logo-oficial.png" alt="ACB" class="h-24 w-24 object-contain opacity-90 drop-shadow-[0_0_15px_rgba(212,175,55,0.5)]">
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-[#090105] via-transparent to-transparent pointer-events-none"></div>
                                            </div>

                                            <!-- Nombre y Puntaje Final -->
                                            <div class="p-4 text-center space-y-2 bg-[#090105]">
                                                <h3 class="text-base sm:text-lg font-black text-[#fef9c3] font-cinzel uppercase tracking-wide line-clamp-2 min-h-[2.75rem] flex items-center justify-center card-nombre" title="{{ $primerLugar['nombre'] }}">
                                                    {{ $primerLugar['nombre'] }}
                                                </h3>

                                                @if (!empty($primerLugar['departamento']))
                                                    @php
                                                        $bandera1 = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($primerLugar['departamento']);
                                                    @endphp
                                                    <p class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center justify-center gap-1.5">
                                                        @if ($bandera1)
                                                            <img src="{{ $bandera1 }}" alt="{{ $primerLugar['departamento'] }}" class="h-3.5 w-auto object-cover rounded-[2px] shadow-sm">
                                                        @endif
                                                        <span>{{ $primerLugar['departamento'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="pt-2 border-t border-[#800020]/80 flex items-center justify-center">
                                                    <span class="text-lg sm:text-xl font-black gold-gradient-text font-mono tabular-nums bg-[#1f0511] border border-[#d4af37] px-4 py-1 rounded-xl shadow-lg card-score">
                                                        {{ number_format($primerLugar['nota_final'], 2) }} <span class="text-xs text-amber-300">pts</span>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- ========================= -->
                        <!-- 3° LUGAR (ESCALÓN BRONCE) -->
                        <!-- ========================= -->
                        @if ($tercerLugar)
                            <div class="order-3 flex flex-col items-center w-full">
                                <div class="podio-card-flip-container w-full max-w-[270px]">
                                    <div id="podioCard_3" class="podio-card-inner {{ $lugar3Revelado ? 'is-flipped' : '' }}">
                                        
                                        <!-- CARA TRASERA: MISTERIO CON SIGNO '?' -->
                                        <div class="podio-card-back w-full bg-gradient-to-b from-[#18050e] via-[#0d0207] to-[#050103] rounded-2xl border-2 border-amber-700/60 shadow-2xl overflow-hidden flex flex-col items-center justify-between p-4 min-h-[380px] select-none">
                                            <div class="bg-gradient-to-r from-[#78350f] to-[#451a03] border-b border-amber-800/40 py-2 px-3 flex items-center justify-between w-full rounded-xl">
                                                <span class="text-2xl font-black font-cinzel text-amber-400 tracking-wider">3</span>
                                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-300 font-cinzel">Tercer Lugar</span>
                                            </div>

                                            <div class="my-auto flex flex-col items-center justify-center py-6">
                                                <div class="relative flex items-center justify-center mb-3">
                                                    <div class="absolute inset-0 rounded-full bg-amber-600/10 blur-xl animate-pulse"></div>
                                                    <svg class="w-20 h-20 text-amber-500 drop-shadow-[0_0_15px_rgba(217,119,6,0.3)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10" class="stroke-amber-800 fill-slate-950/80"></circle>
                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                        <line x1="12" y1="17" x2="12.01" y2="17" stroke-width="2.5"></line>
                                                    </svg>
                                                </div>
                                                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-amber-400/90 font-cinzel animate-pulse">
                                                    En Espera
                                                </span>
                                            </div>

                                            <div class="w-full text-center py-1.5 border-t border-[#3b000b]">
                                                <span class="text-[8px] uppercase tracking-widest text-amber-600/60 font-mono">XXXIII Aquí Canta Bolivia</span>
                                            </div>
                                        </div>

                                        <!-- CARA DELANTERA: GANADOR REVELADO -->
                                        <div class="podio-card-front absolute inset-0 w-full bg-gradient-to-b from-[#1a080f] via-[#0e0308] to-[#050103] rounded-2xl border-2 border-amber-700/60 shadow-2xl overflow-hidden cursor-pointer flex flex-col justify-between"
                                             data-card-id="{{ $tercerLugar['id'] }}"
                                             onclick="abrirModalDesglose({{ $tercerLugar['id'] }})">
                                            
                                            <!-- Insignia Número 3 -->
                                            <div class="bg-gradient-to-r from-[#78350f] to-[#451a03] border-b border-amber-800/40 py-2 px-3 flex items-center justify-between">
                                                <span class="text-2xl font-black font-cinzel text-amber-400 tracking-wider">3</span>
                                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-300 font-cinzel">Tercer Lugar</span>
                                            </div>

                                            <!-- Imagen del Participante -->
                                            <div class="relative w-full aspect-[4/4] bg-[#050103] overflow-hidden">
                                                @if ($tercerLugar['foto_url'])
                                                    <img src="{{ $tercerLugar['foto_url'] }}" alt="{{ $tercerLugar['nombre'] }}" class="w-full h-full object-cover object-top card-foto">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-[radial-gradient(ellipse_at_center,rgba(180,83,9,0.3),rgba(5,1,3,0.95)_70%)] p-4">
                                                        <img src="/images/logo-oficial.png" alt="ACB" class="h-20 w-20 object-contain opacity-80">
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-[#050103] via-transparent to-transparent pointer-events-none"></div>
                                            </div>

                                            <!-- Nombre y Puntaje Final -->
                                            <div class="p-4 text-center space-y-2 bg-[#050103]">
                                                <h3 class="text-sm sm:text-base font-bold text-white font-cinzel uppercase tracking-wide line-clamp-2 min-h-[2.5rem] flex items-center justify-center card-nombre" title="{{ $tercerLugar['nombre'] }}">
                                                    {{ $tercerLugar['nombre'] }}
                                                </h3>

                                                @if (!empty($tercerLugar['departamento']))
                                                    @php
                                                        $bandera3 = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($tercerLugar['departamento']);
                                                    @endphp
                                                    <p class="text-[11px] font-semibold text-amber-400/90 uppercase tracking-wider flex items-center justify-center gap-1.5">
                                                        @if ($bandera3)
                                                            <img src="{{ $bandera3 }}" alt="{{ $tercerLugar['departamento'] }}" class="h-3.5 w-auto object-cover rounded-[2px] shadow-sm">
                                                        @endif
                                                        <span>{{ $tercerLugar['departamento'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="pt-2 border-t border-[#3b000b] flex items-center justify-center">
                                                    <span class="text-base sm:text-lg font-black text-amber-400 font-mono tabular-nums bg-[#15040a] border border-amber-800/60 px-3 py-1 rounded-xl shadow-inner card-score">
                                                        {{ number_format($tercerLugar['nota_final'], 2) }} <span class="text-[10px] text-amber-500">pts</span>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="order-3 hidden sm:block"></div>
                        @endif

                    </div>
                    @endif

                </div>

                <!-- Columna Derecha: Listado General de Participantes (4°, 5°, 6°, etc.) -->
                <div id="columnaListadoGeneral" class="w-full lg:w-[320px] xl:w-[380px] shrink-0 bg-gradient-to-b from-[#14040a] via-[#090205] to-[#070104] rounded-3xl border border-[#6b0015]/60 p-5 shadow-2xl space-y-4 transition-all duration-700 {{ $mostrarGeneral ? 'opacity-100 translate-y-0' : 'hidden opacity-0 translate-y-4' }}">
                    
                    <div class="border-b border-[#3b000b] pb-3 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-[0.25em] text-[#d4af37] font-cinzel block">
                                Posiciones Oficiales
                            </span>
                            <h3 class="text-sm font-bold text-white font-cinzel uppercase tracking-wider">
                                Clasificación General
                            </h3>
                        </div>
                        <span class="text-xs font-mono text-slate-400 bg-[#070104] px-2 py-0.5 rounded border border-[#3b000b]">
                            {{ $ranking->count() }} participantes
                        </span>
                    </div>

                    @if ($restoParticipantes->isEmpty())
                        <p class="text-xs text-slate-400 text-center py-8">
                            No hay más participantes en esta categoría.
                        </p>
                    @else
                        <div class="space-y-2">
                            @foreach ($restoParticipantes as $p)
                                <div class="p-2.5 rounded-xl bg-[#070104]/80 border border-[#3b000b]/80 hover:border-[#6b0015] flex items-center justify-between gap-3 transition cursor-pointer group"
                                     data-card-id="{{ $p['id'] }}"
                                     onclick="abrirModalDesglose({{ $p['id'] }})">
                                    
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <!-- Posición -->
                                        <span class="w-6 h-6 rounded-lg bg-[#1a050d] border border-[#6b0015] text-[11px] font-bold font-mono text-slate-300 flex items-center justify-center shrink-0">
                                            {{ $p['posicion'] }}°
                                        </span>

                                        <!-- Foto / Avatar miniatura -->
                                        @if ($p['foto_url'])
                                            <img src="{{ $p['foto_url'] }}" alt="{{ $p['nombre'] }}" class="w-8 h-8 rounded-lg object-cover border border-[#6b0015] shrink-0">
                                        @endif

                                        <div class="truncate">
                                            <h4 class="text-xs font-bold text-slate-200 font-cinzel truncate group-hover:text-amber-200 transition" title="{{ $p['nombre'] }}">
                                                {{ $p['nombre'] }}
                                            </h4>
                                            @php
                                                $banderaP = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($p['departamento']);
                                            @endphp
                                            <span class="text-[9px] text-slate-400 uppercase tracking-wider flex items-center gap-1 mt-0.5">
                                                @if ($banderaP)
                                                    <img src="{{ $banderaP }}" alt="{{ $p['departamento'] }}" class="h-2.5 w-auto object-cover rounded-[1px] inline-block">
                                                @endif
                                                <span>{{ $p['departamento'] ?? 'Bolivia' }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Puntaje Final -->
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-bold text-amber-300 font-mono tabular-nums">
                                            {{ $p['nota_final'] !== null ? number_format($p['nota_final'], 2) : '—' }}
                                        </span>
                                        <span class="text-[8px] text-slate-500 block">pts</span>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>

            </div>

        <!-- ============================================================== -->
        <!-- MODO 2: RETRATOS DE GALA EN EVALUACIÓN (CUANDO AÚN NO REVELADO) -->
        <!-- ============================================================== -->
        @else
            
            @if ($ranking->isEmpty())
                <div class="bg-[#0e0307]/80 rounded-2xl border border-[#6b0015]/40 p-16 text-center space-y-3 shadow-2xl max-w-xl mx-auto">
                    <span class="text-4xl text-[#d4af37]">🎵</span>
                    <h3 class="text-base font-bold text-white font-cinzel">No hay participaciones para la categoría activa</h3>
                    <p class="text-xs text-slate-400">El jurado delegado de mesa iniciará las evaluaciones para esta categoría.</p>
                </div>
            @else
                <!-- BEGIN: ContestantsGrid -->
                <main class="w-full max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 md:gap-6 justify-items-center relative z-10" data-purpose="desktop-showcase-grid">
                    @foreach ($ranking as $item)
                        @php
                            $pos = $item['posicion'];
                            $numero = is_numeric($pos) ? $pos : $loop->iteration;
                            $foto = $item['foto_url'] ?: null;
                            $bandera = \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($item['departamento']);
                        @endphp

                        <x-participante-card
                            :id="$item['id']"
                            :numero="$numero"
                            :nombre="$item['nombre']"
                            :departamento="$item['departamento']"
                            :banderaUrl="$bandera"
                            :fotoUrl="$foto"
                            :notaFinal="$item['nota_final']"
                            onclick="abrirModalDesglose({{ $item['id'] }})"
                        />
                    @endforeach
                </main>
                <!-- END: ContestantsGrid -->
            @endif

        @endif

        <!-- ============================================================== -->
        <!-- MODALES DE DESGLOSE DE EVALUACIÓN (5 JURADOS)                  -->
        <!-- ============================================================== -->
        @foreach ($ranking as $item)
            <div id="modalDesglose_{{ $item['id'] }}" class="fixed inset-0 z-50 flex items-center justify-center bg-[#050204]/85 backdrop-blur-md hidden p-3 sm:p-4" onclick="if(event.target === this) cerrarModalDesglose({{ $item['id'] }})">
                <div class="bg-[#0f0308] border border-[#6b0015] rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl space-y-3 text-left relative overflow-hidden" onclick="event.stopPropagation()">
                    
                    <div class="corner-bracket-tl"></div>
                    <div class="corner-bracket-tr"></div>
                    <div class="corner-bracket-bl"></div>
                    <div class="corner-bracket-br"></div>

                    <div class="flex items-center justify-between border-b border-[#3b000b] pb-2.5">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#d4af37] font-cinzel">Mesa Evaluadora Oficial</span>
                            <h4 class="text-base font-bold text-white uppercase font-cinzel">{{ $item['nombre'] }}</h4>
                        </div>
                        <button onclick="cerrarModalDesglose({{ $item['id'] }})" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                            ✕
                        </button>
                    </div>

                    <!-- Lista de Jurados y Puntajes -->
                    <div class="space-y-1.5 pt-1">
                        @foreach ($item['jurados'] as $j)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-[#070104] border border-[#3b000b]">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-[#1a050d] border border-[#6b0015] text-[10px] font-bold text-[#d4af37] flex items-center justify-center">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="text-xs text-slate-300 font-medium">
                                        {{ $j['jurado_nombre'] ?? $j['nombre_jurado'] ?? 'Jurado' }}
                                    </span>
                                    @if (!empty($j['es_secreto']) && empty($j['es_revelado']))
                                        <span class="text-[9px] bg-[#3b000b] text-amber-300 border border-[#800020] px-1.5 py-0.2 rounded font-mono font-bold">
                                            🔒 Reservado
                                        </span>
                                    @endif
                                </div>

                                <div class="text-right">
                                    @if (!empty($j['es_secreto']) && empty($j['es_revelado']))
                                        <span class="text-xs font-semibold text-purple-400 italic">Reservada</span>
                                    @elseif (isset($j['nota_total']) && $j['nota_total'] !== null)
                                        <span class="text-xs font-bold font-mono text-amber-300">
                                            {{ is_numeric($j['nota_total']) ? number_format($j['nota_total'], 2) : $j['nota_total'] }}
                                        </span>
                                        <span class="text-[9px] text-slate-500">pts</span>
                                    @else
                                        <span class="text-xs text-slate-500 italic">Pendiente</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Promedio / Nota Final -->
                    <div class="pt-2 border-t border-[#3b000b] flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-300 uppercase font-cinzel tracking-wider">Promedio Final Oficial:</span>
                        <span class="text-sm font-black gold-gradient-text font-mono tabular-nums">
                            {{ $item['nota_final'] !== null ? number_format($item['nota_final'], 2) . ' pts' : 'En Evaluación' }}
                        </span>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

    <!-- ============================================================== -->
    <!-- SCRIPTS DE ACTUALIZACIÓN EN VIVO (WEBSOCKETS Y ANIMACIÓN FLIP) -->
    <!-- ============================================================== -->
    <script>
        function abrirModalDesglose(id) {
            const modal = document.getElementById('modalDesglose_' + id);
            if (modal) modal.classList.remove('hidden');
        }

        function cerrarModalDesglose(id) {
            const modal = document.getElementById('modalDesglose_' + id);
            if (modal) modal.classList.add('hidden');
        }

        // ==============================================================
        // MOTOR DE ANIMACIÓN FLIP ULTRA-FLUIDO EN TIEMPO REAL (60/120 FPS)
        // ==============================================================
        
        // Interpolador numérico suave para actualización de notas
        function interpolarPuntaje(element, valorInicial, valorFinal, duracion = 500) {
            if (isNaN(valorInicial) || isNaN(valorFinal)) {
                element.textContent = isNaN(valorFinal) ? 'N/C' : valorFinal.toFixed(1);
                return;
            }
            if (Math.abs(valorFinal - valorInicial) < 0.05) {
                element.textContent = valorFinal.toFixed(1);
                return;
            }

            const inicio = performance.now();
            function paso(ahora) {
                const progreso = Math.min((ahora - inicio) / duracion, 1);
                // Easing cúbico desacelerado suave
                const factor = 1 - Math.pow(1 - progreso, 3);
                const actual = valorInicial + (valorFinal - valorInicial) * factor;
                element.textContent = actual.toFixed(1);
                if (progreso < 1) {
                    requestAnimationFrame(paso);
                } else {
                    element.textContent = valorFinal.toFixed(1);
                }
            }
            requestAnimationFrame(paso);
        }

        function animarReordenamientoCards(gridContainer, accionReordenar) {
            if (!gridContainer) return;

            // 1. FIRST: Lectura por lote (Batch Read) sin mutaciones intermedias
            const cards = Array.from(gridContainer.querySelectorAll('.musician-card[data-card-id]'));
            const firstRects = new Map();
            for (let i = 0; i < cards.length; i++) {
                const id = cards[i].getAttribute('data-card-id');
                if (id) {
                    firstRects.set(id, cards[i].getBoundingClientRect());
                }
            }

            // 2. MUTACIÓN POR LOTE: Reordenamiento y actualización de datos en el DOM
            accionReordenar();

            // 3. LAST: Segunda lectura por lote (Batch Read 2)
            const currentCards = Array.from(gridContainer.querySelectorAll('.musician-card[data-card-id]'));
            const movingCards = [];

            for (let i = 0; i < currentCards.length; i++) {
                const card = currentCards[i];
                const id = card.getAttribute('data-card-id');
                if (id && firstRects.has(id)) {
                    const first = firstRects.get(id);
                    const last = card.getBoundingClientRect();
                    const dx = first.left - last.left;
                    const dy = first.top - last.top;

                    if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
                        movingCards.push({ card, dx, dy });
                    }
                }
            }

            // 4. INVERT: Escritura por lote de transformaciones iniciales
            for (let i = 0; i < movingCards.length; i++) {
                const item = movingCards[i];
                item.card.classList.add('is-static');
                item.card.style.transform = `translate3d(${item.dx}px, ${item.dy}px, 0)`;
            }

            // 5. PLAY: Disparar reflow único y transición GPU en frame sincronizado
            if (movingCards.length > 0) {
                void gridContainer.offsetHeight;

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        for (let i = 0; i < movingCards.length; i++) {
                            const card = movingCards[i].card;
                            card.classList.remove('is-static');
                            card.classList.add('is-reordering');
                            card.style.transform = 'translate3d(0, 0, 0)';
                        }

                        setTimeout(() => {
                            for (let i = 0; i < movingCards.length; i++) {
                                const card = movingCards[i].card;
                                card.classList.remove('is-reordering');
                                card.style.transform = '';
                            }
                        }, 800);
                    });
                });
            }
        }

        // Procesar evento WebSocket directo sin peticiones de red
        function procesarRankingWebSocket(data) {
            if (!data || !Array.isArray(data.ranking)) {
                refrescarRankingEnVivo();
                return;
            }

            const gridContainer = document.querySelector('[data-purpose="desktop-showcase-grid"]');
            if (!gridContainer) {
                // Si estamos en vista de podio o estado finalizado, refrescar por fetch
                refrescarRankingEnVivo();
                return;
            }

            animarReordenamientoCards(gridContainer, () => {
                data.ranking.forEach((item, index) => {
                    const card = gridContainer.querySelector(`.musician-card[data-card-id="${item.id}"]`);
                    if (card) {
                        // Actualizar puntaje de forma interpolada con resplandor
                        const scoreEl = card.querySelector('.card-score-value');
                        let numFinal = NaN;
                        if (item.nota_final !== null && item.nota_final !== undefined && item.nota_final !== '') {
                            numFinal = parseFloat(item.nota_final);
                        }

                        if (scoreEl) {
                            const textoActual = scoreEl.textContent.trim();
                            const numInicial = parseFloat(textoActual);
                            const notaStr = isNaN(numFinal) ? 'N/C' : numFinal.toFixed(1);

                            if (textoActual !== notaStr) {
                                if (!isNaN(numInicial) && !isNaN(numFinal)) {
                                    interpolarPuntaje(scoreEl, numInicial, numFinal, 500);
                                } else {
                                    scoreEl.textContent = notaStr;
                                }
                                card.classList.add('score-updated-pulse');
                                setTimeout(() => card.classList.remove('score-updated-pulse'), 1300);
                            }
                        }

                        // Actualizar número de posición
                        const numEl = card.querySelector('.card-numero-value');
                        const pos = item.posicion !== undefined && item.posicion !== null ? item.posicion : (index + 1);
                        if (numEl) {
                            numEl.textContent = 'N° ' + pos;
                        }

                        // Reordenar nodo DOM en el contenedor
                        gridContainer.appendChild(card);
                    }
                });
            });
        }

        // Actualización inteligente del DOM con preservación de nodos
        let actualizandoContenido = false;
        async function refrescarRankingEnVivo() {
            if (actualizandoContenido) return;
            actualizandoContenido = true;

            try {
                const fetchUrl = new URL(window.location.href);
                fetchUrl.searchParams.set('_t', Date.now());

                const response = await fetch(fetchUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });

                if (response.ok) {
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const nuevoContenido = doc.getElementById('rankingLiveWrapper');
                    const contenedorActual = document.getElementById('rankingLiveWrapper');

                    if (nuevoContenido && contenedorActual) {
                        const gridActual = contenedorActual.querySelector('[data-purpose="desktop-showcase-grid"]');
                        const gridNuevo = nuevoContenido.querySelector('[data-purpose="desktop-showcase-grid"]');

                        // Si ambos son modo Gala Grid, reordenar en sitio preservando nodos
                        if (gridActual && gridNuevo) {
                            animarReordenamientoCards(gridActual, () => {
                                const newCards = Array.from(gridNuevo.querySelectorAll('.musician-card[data-card-id]'));
                                newCards.forEach(newCard => {
                                    const id = newCard.getAttribute('data-card-id');
                                    const existingCard = gridActual.querySelector(`.musician-card[data-card-id="${id}"]`);
                                    if (existingCard) {
                                        // Actualizar textos/puntajes
                                        const oldScore = existingCard.querySelector('.card-score-value');
                                        const newScore = newCard.querySelector('.card-score-value');
                                        if (oldScore && newScore && oldScore.textContent.trim() !== newScore.textContent.trim()) {
                                            const nInit = parseFloat(oldScore.textContent.trim());
                                            const nEnd = parseFloat(newScore.textContent.trim());
                                            if (!isNaN(nInit) && !isNaN(nEnd)) {
                                                interpolarPuntaje(oldScore, nInit, nEnd, 500);
                                            } else {
                                                oldScore.textContent = newScore.textContent.trim();
                                            }
                                            existingCard.classList.add('score-updated-pulse');
                                            setTimeout(() => existingCard.classList.remove('score-updated-pulse'), 1300);
                                        }

                                        const oldNum = existingCard.querySelector('.card-numero-value');
                                        const newNum = newCard.querySelector('.card-numero-value');
                                        if (oldNum && newNum) {
                                            oldNum.textContent = newNum.textContent.trim();
                                        }

                                        gridActual.appendChild(existingCard);
                                    } else {
                                        gridActual.appendChild(newCard.cloneNode(true));
                                    }
                                });
                            });

                            // Actualizar modales de desglose
                            const modalesNuevos = nuevoContenido.querySelectorAll('[id^="modalDesglose_"]');
                            modalesNuevos.forEach(m => {
                                const mActual = document.getElementById(m.id);
                                if (mActual) {
                                    const estaAbierto = !mActual.classList.contains('hidden');
                                    mActual.innerHTML = m.innerHTML;
                                    if (estaAbierto) mActual.classList.remove('hidden');
                                } else {
                                    contenedorActual.appendChild(m.cloneNode(true));
                                }
                            });
                        } else {
                            // Cambio de modo (e.g. paso a podio de gala)
                            const modalesAbiertos = [];
                            document.querySelectorAll('[id^="modalDesglose_"]:not(.hidden)').forEach(m => {
                                modalesAbiertos.push(m.id);
                            });

                            contenedorActual.innerHTML = nuevoContenido.innerHTML;

                            modalesAbiertos.forEach(id => {
                                const modal = document.getElementById(id);
                                if (modal) modal.classList.remove('hidden');
                            });
                        }
                    }
                }
            } catch (err) {
                console.warn('Error al actualizar contenido en vivo:', err);
            } finally {
                actualizandoContenido = false;
            }
        }

        // Función de revelación de paso del podio (animación 3D Flip Card)
        function aplicarPasoPodio(data) {
            if (!data) return;
            const paso = parseInt(data.paso, 10);
            const pos = data.posicion_revelada ? parseInt(data.posicion_revelada, 10) : null;

            const card1 = document.getElementById('podioCard_1');
            const card2 = document.getElementById('podioCard_2');
            const card3 = document.getElementById('podioCard_3');
            const colGeneral = document.getElementById('columnaListadoGeneral');

            if (paso === 0) {
                // Voltear todas las cartas a misterio y ocultar listado general
                if (card1) card1.classList.remove('is-flipped');
                if (card2) card2.classList.remove('is-flipped');
                if (card3) card3.classList.remove('is-flipped');
                if (colGeneral) {
                    colGeneral.classList.add('hidden', 'opacity-0', 'translate-y-4');
                    colGeneral.classList.remove('opacity-100', 'translate-y-0');
                }
                return;
            }

            // Inyectar datos del ganador si viene en el payload
            if (data.ganador && pos) {
                const cardTarget = document.getElementById(`podioCard_${pos}`);
                if (cardTarget) {
                    const front = cardTarget.querySelector('.podio-card-front');
                    if (front) {
                        front.setAttribute('data-card-id', data.ganador.id);
                        front.onclick = () => abrirModalDesglose(data.ganador.id);
                        const nombreEl = front.querySelector('.card-nombre');
                        if (nombreEl) {
                            nombreEl.textContent = data.ganador.nombre;
                            nombreEl.title = data.ganador.nombre;
                        }
                        const scoreEl = front.querySelector('.card-score');
                        if (scoreEl) {
                            const nf = parseFloat(data.ganador.nota_final);
                            scoreEl.innerHTML = (isNaN(nf) ? '0.00' : nf.toFixed(2)) + ' <span class="text-[10px] text-amber-300">pts</span>';
                        }
                    }
                }
            }

            if (paso === 3) {
                if (card3) card3.classList.add('is-flipped');
                if (card2) card2.classList.remove('is-flipped');
                if (card1) card1.classList.remove('is-flipped');
                if (colGeneral) {
                    colGeneral.classList.add('hidden', 'opacity-0', 'translate-y-4');
                    colGeneral.classList.remove('opacity-100', 'translate-y-0');
                }
            } else if (paso === 2) {
                if (card3) card3.classList.add('is-flipped');
                if (card2) card2.classList.add('is-flipped');
                if (card1) card1.classList.remove('is-flipped');
                if (colGeneral) {
                    colGeneral.classList.add('hidden', 'opacity-0', 'translate-y-4');
                    colGeneral.classList.remove('opacity-100', 'translate-y-0');
                }
            } else if (paso === 1) {
                if (card3) card3.classList.add('is-flipped');
                if (card2) card2.classList.add('is-flipped');
                if (card1) card1.classList.add('is-flipped');

                // Revelar listado general con animación fluida
                if (colGeneral && colGeneral.classList.contains('hidden')) {
                    colGeneral.classList.remove('hidden');
                    setTimeout(() => {
                        colGeneral.classList.remove('opacity-0', 'translate-y-4');
                        colGeneral.classList.add('opacity-100', 'translate-y-0');
                    }, 50);
                }
            }
        }

        // Suscripción a WebSockets
        function iniciarEscuchaEcho() {
            if (!window.Echo) return false;

            try {
                const canalRanking = window.Echo.channel('ranking');
                if (canalRanking) {
                    canalRanking.listen('.ranking.actualizado', (data) => {
                        procesarRankingWebSocket(data);
                    });
                    canalRanking.listen('ranking.actualizado', (data) => {
                        procesarRankingWebSocket(data);
                    });
                    canalRanking.listen('.podio.paso_revelado', (data) => {
                        aplicarPasoPodio(data);
                    });
                    canalRanking.listen('podio.paso_revelado', (data) => {
                        aplicarPasoPodio(data);
                    });
                    
                    ['.calificacion.registrada', 'calificacion.registrada', '.calificacion.corregida', 'calificacion.corregida', '.jurado_secreto.revelado', 'jurado_secreto.revelado', '.categoria.finalizada', 'categoria.finalizada', '.categoria.activa.cambiada', 'categoria.activa.cambiada', '.presentacion.estado.cambiado', 'presentacion.estado.cambiado'].forEach(ev => {
                        canalRanking.listen(ev, (data) => {
                            if (data && data.ranking) {
                                procesarRankingWebSocket(data);
                            } else {
                                refrescarRankingEnVivo();
                            }
                        });
                    });
                }

                const canalPresentaciones = window.Echo.channel('presentaciones');
                if (canalPresentaciones) {
                    canalPresentaciones.listen('.podio.paso_revelado', (data) => {
                        aplicarPasoPodio(data);
                    });
                    canalPresentaciones.listen('podio.paso_revelado', (data) => {
                        aplicarPasoPodio(data);
                    });

                    ['.calificacion.registrada', 'calificacion.registrada', '.calificacion.corregida', 'calificacion.corregida', '.presentacion.estado.cambiado', 'presentacion.estado.cambiado', '.cronometro.actualizado', 'cronometro.actualizado', '.categoria.activa.cambiada', 'categoria.activa.cambiada'].forEach(ev => {
                        canalPresentaciones.listen(ev, (data) => {
                            if (data && data.ranking) {
                                procesarRankingWebSocket(data);
                            } else {
                                refrescarRankingEnVivo();
                            }
                        });
                    });
                }

                return true;
            } catch (e) {
                console.warn('Error suscribiendo a canales Echo:', e);
                return false;
            }
        }

        function setupLiveSync() {
            if (!iniciarEscuchaEcho()) {
                let attempts = 0;
                const echoInterval = setInterval(function () {
                    attempts++;
                    if (iniciarEscuchaEcho() || attempts > 30) {
                        clearInterval(echoInterval);
                    }
                }, 250);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupLiveSync);
        } else {
            setupLiveSync();
        }
        window.addEventListener('load', setupLiveSync);

        // Polling de respaldo de baja frecuencia para evitar colisiones
        setInterval(refrescarRankingEnVivo, 8000);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id^="modalDesglose_"]').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>

</x-layouts.publico>