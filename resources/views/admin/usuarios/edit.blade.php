<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Editar usuario</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="max-w-3xl">
        @csrf
        @method('PATCH')
        @include('admin.usuarios._form', ['editableRol' => $usuario->id !== auth()->id()])
    </form>
</x-layouts.admin>