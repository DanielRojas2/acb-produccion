<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Registrar inscripción</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <form method="POST" action="{{ route('admin.inscripciones.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.inscripciones._form')
    </form>
</x-layouts.admin>