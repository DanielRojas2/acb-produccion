<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Editar participante</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.participantes.update', $participante) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        @include('admin.participantes._form')
    </form>

    @if ($participante->inscripciones->isNotEmpty())
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Inscripciones donde figura</h3>
            <ul class="text-sm text-slate-600 space-y-1">
                @foreach ($participante->inscripciones as $inscripcion)
                    <li>
                        <span class="font-medium text-slate-800">{{ $inscripcion->presentacion?->nombre_presentacion ?? 'Presentación' }}</span>
                        — {{ $inscripcion->categoria?->categoria ?? '—' }}
                        · {{ $inscripcion->fase?->fase?->label() ?? '—' }}
                        @if ($inscripcion->departamento !== null)
                            · {{ $inscripcion->departamento->nombre }}
                        @endif
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-slate-400">
                Este participante no puede eliminarse mientras figure en inscripciones.
            </p>
        </div>
    @endif
</x-layouts.admin>