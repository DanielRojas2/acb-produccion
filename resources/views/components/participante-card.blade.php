@props([
    'id' => null,
    'numero' => 1,
    'nombre' => '',
    'departamento' => null,
    'banderaUrl' => null,
    'fotoUrl' => null,
    'notaFinal' => null,
    'onclick' => null,
])

@php
    $bandera = $banderaUrl ?? \App\Modules\Departamentos\Models\Departamento::urlBanderaPorNombre($departamento);
    $foto = $fotoUrl ?: asset('images/fondo.png');

    if ($notaFinal === null || $notaFinal === '' || $notaFinal === false) {
        $notaTexto = 'N/C';
    } else {
        $val = (float) $notaFinal;
        $notaTexto = (round($val, 1) == $val) ? number_format($val, 1) : number_format($val, 1);
    }
@endphp

<article {{ $attributes->merge(['class' => 'musician-card relative w-full max-w-[270px] xl:max-w-[245px] aspect-[3/4.6] rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-b from-stone-900/90 via-[#120f0c] to-black transition-all duration-300 hover:shadow-gold-500/20 hover:-translate-y-1 flex flex-col justify-between p-3 group cursor-pointer']) }}
         style="aspect-ratio: 3 / 4.6; min-height: 380px;"
         data-purpose="musician-score-card"
         @if($id) id="card_participante_{{ $id }}" data-card-id="{{ $id }}" @endif
         @if($onclick) onclick="{{ $onclick }}" @endif>
    <!-- Remates angulares dorados -->
    <div class="corner-bracket corner-tl"></div>
    <div class="corner-bracket corner-tr"></div>
    <div class="corner-bracket corner-bl"></div>
    <div class="corner-bracket corner-br"></div>

    <!-- Fotografía del Participante / Fondo de Gala -->
    <img class="absolute inset-0 w-full h-full object-cover z-0"
         src="{{ $foto }}"
         alt="{{ $nombre }}"
         onerror="this.onerror=null; this.src='{{ asset('images/fondo.png') }}';">

    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-b from-stone-900/55 via-[#15120e]/55 to-black/95 z-[1] pointer-events-none" style="background: linear-gradient(to bottom, rgba(28, 25, 23, 0.55), rgba(21, 18, 14, 0.55), rgba(0, 0, 0, 0.95));"></div>
    <div aria-hidden="true" class="absolute inset-0 opacity-15 card-dot-pattern z-[2] pointer-events-none"></div>

    <!-- Header con Número y Bandera -->
    <header class="relative z-10 px-4 pt-4 flex flex-col gap-2" data-purpose="card-top-category">
        <div class="flex items-center justify-between">
            <span class="card-numero-value text-[12px] tracking-[0.2em] uppercase font-bold text-gold-400/90 drop-shadow-md" style="color: #fbbf24;">
                N° {{ $numero }}
            </span>
            @if ($bandera)
                <img class="w-3.5 h-3.5 object-cover" src="{{ $bandera }}" alt="Bandera de {{ $departamento ?? 'Bolivia' }}">
            @endif
        </div>
        <div class="w-full h-px" style="background: linear-gradient(to right, transparent, rgba(212, 175, 55, 0.4), transparent);"></div>
    </header>

    <!-- Parte baja: Calificación sobre el Nombre y Departamento -->
    <div class="relative z-10 px-3 pb-4 mt-auto flex flex-col items-center text-center" data-purpose="artist-info-and-score">
        <div class="flex items-baseline gap-1 mb-1">
            <span class="card-score-value font-serif text-4xl sm:text-5xl font-extrabold tracking-tight gold-gradient-text drop-shadow-[0_2px_12px_rgba(212,175,55,0.45)]">
                {{ $notaTexto }}
            </span>
            <span class="text-[11px] font-semibold text-yellow-200/50 uppercase tracking-wider font-sans">/100</span>
        </div>
        <div aria-hidden="true" class="w-8 h-0.5 rounded-full mb-2" style="background-color: rgba(212, 175, 55, 0.5);"></div>
        <h2 class="font-serif text-xl font-bold tracking-widest text-amber-50 uppercase drop-shadow-md line-clamp-1" title="{{ $nombre }}">
            {{ $nombre }}
        </h2>
        <span class="flex bandera-bolivia justify-items-center text-xs text-stone-300 font-sans mt-0.5">
            {{ $departamento ?? 'Bolivia' }}
        </span>
    </div>
</article>
