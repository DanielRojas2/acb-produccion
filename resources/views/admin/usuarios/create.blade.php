<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Nuevo usuario</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="max-w-3xl">
        @csrf
        @include('admin.usuarios._form', ['editableRol' => true])
    </form>
</x-layouts.admin>