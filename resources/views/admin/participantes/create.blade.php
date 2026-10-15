<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Registrar participante</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <form method="POST" action="{{ route('admin.participantes.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.participantes._form')
    </form>
</x-layouts.admin>