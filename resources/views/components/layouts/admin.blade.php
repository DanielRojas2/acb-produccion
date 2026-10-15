<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ACB Canta Bolivia') }} — Administración</title>

        <link rel="icon" type="image/png" href="/images/logo-oficial.png">
        <link rel="shortcut icon" href="/images/favicon.ico">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="font-sans antialiased min-h-screen bg-slate-100">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-64 shrink-0 bg-slate-900 text-slate-200 flex flex-col">
                <div class="px-6 py-5 flex items-center gap-3 border-b border-slate-800">
                    <img src="/images/logo-oficial.png" alt="ACB" class="h-10 w-10 object-contain">
                    <div>
                        <p class="font-bold text-white leading-tight">ACB Canta Bolivia</p>
                        <p class="text-xs text-slate-400">Administración</p>
                    </div>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                    {{ $sidebar }}
                </nav>

                <div class="px-6 py-4 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-slate-700 flex items-center justify-center uppercase text-white font-bold">
                            {{ substr(auth()->user()->nombre ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-100">{{ auth()->user()->nombre }} {{ auth()->user()->paterno }}</p>
                            <p class="text-xs text-slate-400">Admin</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-left text-xs text-slate-400 hover:text-white py-1">
                            Cerrar sesión →
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main -->
            <div class="flex-1 flex flex-col min-w-0">
                @isset($header)
                    <header class="bg-white shadow-sm border-b border-slate-200">
                        <div class="px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>