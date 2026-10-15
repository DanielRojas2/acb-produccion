<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Editar inscripción</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inscripciones.update', $inscripcion) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        @include('admin.inscripciones._form')
    </form>
</x-layouts.admin>