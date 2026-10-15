<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Configuración del Festival</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Departamentos</h3>
            <ul class="space-y-1 text-sm text-slate-600">
                @foreach ($departamentos as $departamento)
                    <li class="flex items-center justify-between border-b border-slate-100 py-1.5">
                        <div class="flex items-center gap-2">
                            @if ($departamento->bandera_url)
                                <img src="{{ $departamento->bandera_url }}" alt="{{ $departamento->nombre }}" class="h-4 w-6 object-cover rounded shadow-xs border border-slate-200">
                            @endif
                            <span class="font-medium text-slate-700">{{ $departamento->nombre }}</span>
                        </div>
                        <span class="text-xs text-slate-400">{{ $departamento->fases_count }} fase(s)</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Fases</h3>
            <ul class="space-y-1 text-sm text-slate-600">
                @foreach ($fases as $fase)
                    <li class="flex justify-between border-b border-slate-100 py-1">
                        <span class="font-medium text-slate-700">
                            {{ $fase->fase->label() }}
                            @if ($fase->departamento)
                                — {{ $fase->departamento->nombre }}
                            @else
                                <span class="text-xs text-slate-400">(nacional)</span>
                            @endif
                        </span>
                        <span class="text-xs text-slate-400">{{ $fase->fase->value }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Categorías y parámetros
            <span class="ml-2 text-xs font-normal text-slate-400">(suma de máximos = 100 por categoría)</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Categoría</th>
                        <th class="py-2 pr-4">Tiempo</th>
                        <th class="py-2 pr-4">Máx. part.</th>
                        <th class="py-2 pr-4">Parámetro</th>
                        <th class="py-2 pr-4 text-right">Mín.</th>
                        <th class="py-2 text-right">Máx.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        @foreach ($categoria->categoriaParametros as $i => $cp)
                            <tr class="border-b border-slate-100">
                                @if ($i === 0)
                                    <td class="py-2 pr-4 align-top font-semibold text-slate-800" rowspan="{{ $categoria->categoriaParametros->count() }}">
                                        {{ $categoria->categoria }}
                                        <span class="block text-xs font-normal {{ $categoria->sumaEsCien() ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $categoria->categoriaParametros->sum('valor_maximo') }} / 100
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 align-top" rowspan="{{ $categoria->categoriaParametros->count() }}">{{ $categoria->tiempo_presentacion }} min</td>
                                    <td class="py-2 pr-4 align-top" rowspan="{{ $categoria->categoriaParametros->count() }}">{{ $categoria->max_participantes }}</td>
                                @endif
                                <td class="py-2 pr-4 text-slate-600">{{ $cp->parametro->parametro }}</td>
                                <td class="py-2 pr-4 text-right text-slate-500">{{ $cp->valor_minimo }}</td>
                                <td class="py-2 text-right font-medium text-slate-700">{{ $cp->valor_maximo }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>