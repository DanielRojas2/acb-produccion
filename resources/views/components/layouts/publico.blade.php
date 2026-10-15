<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050204]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Aquí Canta Bolivia') }} — XXXIII Festival Nacional</title>

        <link rel="icon" type="image/png" href="/images/logo-oficial.png">
        <link rel="shortcut icon" href="/images/favicon.ico">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800;900&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

        <style>
            .font-cinzel, .font-serif { font-family: 'Cinzel', serif !important; }
            .font-outfit, .font-sans, .font-montserrat { font-family: 'Montserrat', sans-serif !important; }
            
            /* Card Participante - Alto Rendimiento y Aceleración 60/120fps */
            .musician-card {
                will-change: transform;
                transform: translateZ(0);
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                perspective: 1000px;
                contain: layout paint;
            }
            .musician-card:not(.is-reordering):not(.is-static) {
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .musician-card:hover:not(.is-reordering) {
                transform: translateY(-4px) translateZ(0);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 25px 4px rgba(212, 175, 55, 0.35);
            }
            .musician-card.is-static {
                transition: none !important;
            }
            .musician-card.is-reordering {
                transition: transform 0.75s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease !important;
                z-index: 40 !important;
                box-shadow: 0 0 32px 4px rgba(212, 175, 55, 0.45) !important;
            }
            .score-updated-pulse {
                animation: galaScorePulse 1.2s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes galaScorePulse {
                0% { transform: scale(1) translateZ(0); box-shadow: 0 0 0 rgba(212, 175, 55, 0); }
                25% { transform: scale(1.04) translateZ(0); box-shadow: 0 0 32px 5px rgba(251, 191, 36, 0.65); }
                100% { transform: scale(1) translateZ(0); box-shadow: 0 0 0 rgba(212, 175, 55, 0); }
            }
            .corner-bracket {
                position: absolute;
                width: 18px;
                height: 18px;
                border-color: #d4af37 !important;
                border-style: solid !important;
                pointer-events: none;
                z-index: 25;
                transition: border-color 0.3s ease, filter 0.3s ease !important;
            }
            .corner-tl {
                top: 14px;
                left: 14px;
                border-top-width: 2px !important;
                border-left-width: 2px !important;
                border-right-width: 0 !important;
                border-bottom-width: 0 !important;
            }
            .corner-tr {
                top: 14px;
                right: 14px;
                border-top-width: 2px !important;
                border-right-width: 2px !important;
                border-left-width: 0 !important;
                border-bottom-width: 0 !important;
            }
            .corner-bl {
                bottom: 14px;
                left: 14px;
                border-bottom-width: 2px !important;
                border-left-width: 2px !important;
                border-top-width: 0 !important;
                border-right-width: 0 !important;
            }
            .corner-br {
                bottom: 14px;
                right: 14px;
                border-bottom-width: 2px !important;
                border-right-width: 2px !important;
                border-top-width: 0 !important;
                border-left-width: 0 !important;
            }
            .musician-card:hover .corner-bracket,
            .group:hover .corner-bracket {
                border-color: #fde68a !important;
                filter: drop-shadow(0 0 6px rgba(212, 175, 55, 0.85)) !important;
            }
            
            /* Gradientes de oro y carmesí */
            .gold-gradient-text {
                background: linear-gradient(135deg, #FFF8DC 0%, #F5D061 40%, #D4AF37 75%, #AA7C11 100%) !important;
                -webkit-background-clip: text !important;
                -webkit-text-fill-color: transparent !important;
            }
            .bandera-bolivia {
                max-height: 0.7rem;
            }
            .card-dot-pattern {
                background-image: radial-gradient(#d4af37 1px, transparent 1px) !important;
                background-size: 16px 16px !important;
            }

            /* Scrollbars guindo sobrio estilo Oruro */
            ::-webkit-scrollbar {
                width: 5px;
                height: 5px;
            }
            ::-webkit-scrollbar-track {
                background: transparent;
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
            
            <!-- Barra Superior Principal de Gala -->
            <header class="border-b border-[#6b0015]/40 bg-[#070205]/95 backdrop-blur-xl sticky top-0 z-40 shadow-2xl">
                <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-10 py-3.5 flex items-center justify-between gap-4">
                    
                    <!-- Identidad / Logo del Festival (Sin recuadro dorado) -->
                    <div class="flex items-center gap-3.5 cursor-pointer" onclick="window.location.href='{{ route('inicio') }}'">
                        <img src="/images/logo-oficial.png" alt="Aquí Canta Bolivia" class="h-10 w-10 object-contain drop-shadow" onerror="this.src='/images/logo-oficial.png'">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-cinzel text-base sm:text-lg font-black tracking-[0.15em] gold-gradient-text uppercase">
                                    Aquí… Canta Bolivia
                                </span>
                            </div>
                            <p class="text-[10px] sm:text-[11px] font-semibold tracking-[0.2em] text-[#d4af37]/80 uppercase">
                                XXXIII Festival Nacional · Oruro 2026
                            </p>
                        </div>
                    </div>

                    <!-- Estado del Festival en Vivo -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#120409]/90 border border-[#6b0015]/60 text-[11px] text-slate-300 shadow-inner">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span class="font-semibold tracking-wider uppercase text-[10px] text-slate-400">Transmisión Oficial</span>
                            <span class="text-[#6b0015]">|</span>
                            <span class="font-bold text-amber-300 font-cinzel">Teatro Oruro</span>
                        </div>
                        @auth
                            <a href="{{ auth()->user()->esAdmin() ? route('admin.inicio') : route('jurado.inicio') }}"
                               class="text-[11px] font-bold text-amber-200/80 hover:text-white bg-[#1a050d] px-3 py-1 rounded-lg border border-[#6b0015] hover:border-amber-400/50 transition">
                                Panel
                            </a>
                        @endauth
                    </div>

                </div>
            </header>

            <!-- Contenido Principal -->
            <main class="flex-1 max-w-[1700px] w-full mx-auto px-3 sm:px-6 lg:px-10 py-6">
                {{ $slot }}
            </main>

            <!-- Pie de Página de Gala -->
            <footer class="mt-12 border-t border-[#3b000b] bg-[#050204]/90 backdrop-blur-md">
                <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-10 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                    <div class="flex items-center gap-3">
                        <span class="font-cinzel font-bold text-[#d4af37] uppercase tracking-widest text-xs">Aquí Canta Bolivia</span>
                        <span class="text-[#6b0015]">·</span>
                        <span>Gobierno Autónomo Departamental de Oruro</span>
                    </div>

                    <div class="text-center md:text-right font-medium text-slate-400">
                        XXXIII Festival Nacional de la Canción Boliviana · 20 y 21 de noviembre de 2026
                    </div>
                </div>
            </footer>

        </div>
    </body>
</html>