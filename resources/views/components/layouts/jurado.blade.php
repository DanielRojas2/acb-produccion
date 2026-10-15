<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050204]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Aquí Canta Bolivia') }} — Panel de Jurado</title>

        <link rel="icon" type="image/png" href="/images/logo-oficial.png">
        <link rel="shortcut icon" href="/images/favicon.ico">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <style>
            .font-cinzel { font-family: 'Cinzel', serif; }
            .font-outfit { font-family: 'Outfit', sans-serif; }

            body {
                background:
                    radial-gradient(circle at top, rgba(128,0,32,0.38), transparent 38%),
                    linear-gradient(180deg, #050204 0%, #090306 100%);
            }

            .corner-bracket-tl { position: absolute; top: 8px; left: 8px; width: 14px; height: 14px; border-top: 1.5px solid #d4af37; border-left: 1.5px solid #d4af37; pointer-events: none; z-index: 10; }
            .corner-bracket-tr { position: absolute; top: 8px; right: 8px; width: 14px; height: 14px; border-top: 1.5px solid #d4af37; border-right: 1.5px solid #d4af37; pointer-events: none; z-index: 10; }
            .corner-bracket-bl { position: absolute; bottom: 8px; left: 8px; width: 14px; height: 14px; border-bottom: 1.5px solid #d4af37; border-left: 1.5px solid #d4af37; pointer-events: none; z-index: 10; }
            .corner-bracket-br { position: absolute; bottom: 8px; right: 8px; width: 14px; height: 14px; border-bottom: 1.5px solid #d4af37; border-right: 1.5px solid #d4af37; pointer-events: none; z-index: 10; }

            .gold-gradient-text {
                background: linear-gradient(135deg, #fffbeb 0%, #fef08a 25%, #d4af37 60%, #b45309 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .oruro-gradient-text {
                background: linear-gradient(135deg, #fef08a 0%, #d4af37 50%, #e11d48 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .tekken-card-clip {
                clip-path: polygon(18px 0%, 100% 0%, 100% calc(100% - 18px), calc(100% - 18px) 100%, 0% 100%, 0% 18px);
            }
            .tekken-card-clip-sm {
                clip-path: polygon(12px 0%, 100% 0%, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0% 100%, 0% 12px);
            }

            .jurado-brand-mark {
                box-shadow: 0 0 22px rgba(212,175,55,0.18);
            }

            .jurado-header-line {
                background: linear-gradient(90deg, rgba(212,175,55,0), rgba(212,175,55,0.9) 20%, rgba(212,175,55,0.9) 80%, rgba(212,175,55,0));
                height: 1px;
            }

            .jurado-tab {
                border: 1px solid rgba(212,175,55,0.25);
                background: rgba(12, 5, 8, 0.9);
                box-shadow: inset 0 0 0 1px rgba(212,175,55,0.08);
            }

            .jurado-tab.active {
                background: linear-gradient(90deg, rgba(58,8,18,0.96), rgba(52,10,18,0.88), rgba(58,8,18,0.96));
                border-color: rgba(212,175,55,0.75);
                box-shadow: inset 0 0 0 1px rgba(212,175,55,0.25), 0 0 18px rgba(212,175,55,0.16);
            }

            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: rgba(10, 3, 6, 0.6);
            }
            ::-webkit-scrollbar-thumb {
                background: #6b0015;
                border-radius: 9999px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #990024;
            }
            * {
                scrollbar-width: thin;
                scrollbar-color: #6b0015 transparent;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="font-outfit antialiased min-h-screen bg-[#050204] text-slate-100 selection:bg-[#800020] selection:text-amber-200 flex flex-col justify-between overflow-x-hidden">
        
        <!-- Fondo Oruro Gala: Carmesí profundo, Guindo y Negro Obsidiana -->
        <div class="fixed inset-0 pointer-events-none z-0 bg-[radial-gradient(ellipse_100%_80%_at_50%_-15%,rgba(128,0,32,0.45),rgba(5,2,4,0.98))]"></div>

        <div class="relative z-10 flex flex-col min-h-screen justify-between">
            
            <!-- Barra Superior Principal de Gala para Jurados (Sin línea separadora) -->
            <header class="bg-[#070205]/95 backdrop-blur-xl sticky top-0 z-40 shadow-[0_20px_40px_rgba(0,0,0,0.45)] border-b border-[#6b0015]/80">
                <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-10 py-2 sm:py-2.5 flex items-center justify-between gap-4">
                    
                    <!-- Identidad / Logo del Festival -->
                    <div class="jurado-brand-mark flex items-center gap-3 cursor-pointer rounded-full border border-[#6b0015]/60 bg-[#12040a]/80 px-2.5 py-1" onclick="window.location.href='{{ route('jurado.inicio') }}'">
                        <img src="/images/logo-oficial.png" alt="Aquí Canta Bolivia" class="h-8 w-8 object-contain drop-shadow-[0_0_12px_rgba(212,175,55,0.5)]" onerror="this.src='/images/logo-oficial.png'">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-cinzel text-sm sm:text-base font-black tracking-[0.16em] gold-gradient-text uppercase">
                                    Aquí… Canta Bolivia
                                </span>
                            </div>
                            <p class="text-[9px] sm:text-[10px] font-semibold tracking-[0.22em] text-[#f6d77c]/75 uppercase">
                                Panel Oficial de Jurados · Oruro 2026
                            </p>
                        </div>
                    </div>

                    <!-- Enlaces de Navegación y Perfil del Jurado -->
                    <div class="flex items-center gap-3 sm:gap-4">
                        
                        <!-- Accesos directos de navegación -->
                        <nav class="hidden md:flex items-center gap-1.5">
                            <a href="{{ route('jurado.inicio') }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request()->routeIs('jurado.inicio') ? 'bg-[#3b000b] text-amber-300 border border-amber-500/40 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#1a050d] border border-transparent' }}">
                                <span>📋</span>
                                <span>Inicio</span>
                            </a>
                            <a href="{{ route('jurado.presentaciones.actual') }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request()->routeIs('jurado.presentaciones.actual') ? 'bg-[#3b000b] text-amber-300 border border-amber-500/40 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#1a050d] border border-transparent' }}">
                                <span class="inline-block w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <span>Escenario en Vivo</span>
                            </a>
                            <a href="{{ route('inicio') }}" target="_blank"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-amber-200 hover:bg-[#1a050d] transition flex items-center gap-1.5 border border-transparent">
                                <span>🌐</span>
                                <span>Vista Pública</span>
                            </a>
                        </nav>

                        <!-- Perfil Jurado Unificado con Salir -->
                        @auth
                            <div class="pl-3 border-l border-[#6b0015]/60">
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            title="Cerrar sesión"
                                            class="group flex items-center gap-2.5 px-3 py-1 rounded-2xl bg-[#14040a] hover:bg-[#2b0813] border border-[#6b0015]/70 hover:border-rose-500/60 transition-all shadow-md">
                                        <div class="text-left">
                                            <p class="text-xs font-bold text-slate-200 group-hover:text-white leading-tight truncate max-w-[140px]">
                                                {{ auth()->user()->nombre }} {{ auth()->user()->paterno }}
                                            </p>
                                            <p class="text-[9px] text-amber-400/90 font-medium">
                                                @if (auth()->user()->puedeControlarTiempo())
                                                    Delegado de Mesa
                                                @else
                                                    Jurado Oficial
                                                @endif
                                            </p>
                                        </div>
                                        <div class="p-1 rounded-lg bg-[#240610] group-hover:bg-rose-900/60 text-slate-400 group-hover:text-rose-200 border border-[#6b0015]/50 group-hover:border-rose-500/50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        @endauth

                    </div>

                </div>
            </header>

            <!-- Encabezado secundario opcional -->
            @isset($header)
                <div class="border-b border-[#3b000b] bg-[#090306]/90 backdrop-blur-md">
                    <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-10 py-2">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <div class="jurado-header-line w-full"></div>

            <!-- Contenido Principal -->
            <main class="flex-1 max-w-[1700px] w-full mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-4">
                {{ $slot }}
            </main>

            <!-- Pie de Página de Gala Compacto -->
            <footer class="mt-4 border-t border-[#3b000b] bg-[#050204]/90 backdrop-blur-md">
                <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-10 py-2.5 flex flex-col md:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="font-cinzel font-bold text-[#d4af37] uppercase tracking-widest text-[11px]">Aquí Canta Bolivia</span>
                        <span class="text-[#6b0015]">·</span>
                        <span class="text-[11px]">Mesa de Jurados · GADOR 2026</span>
                    </div>

                    <div class="text-center md:text-right font-medium text-slate-500 text-[10px]">
                        XXXIII Festival Nacional de la Canción Boliviana · Teatro Internacional Oruro
                    </div>
                </div>
            </footer>

        </div>
    </body>
</html>