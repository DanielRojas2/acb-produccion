<x-layouts.admin>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.calificaciones.presentacion', $calificacion->juradoPresentacion->id_presentacion) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 mb-1 inline-flex items-center gap-1">
                    ← Volver al Detalle de Calificaciones
                </a>
                <h2 class="text-xl font-bold text-slate-800">Historial de Correcciones y Auditoría</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full border border-purple-200">
                    Registro de Auditoría
                </span>
            </div>
        </div>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Tarjeta de Contexto -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold block">Presentación</span>
                    <h3 class="text-lg font-bold text-slate-900">{{ $calificacion->juradoPresentacion?->presentacion?->nombreMostrado() }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Categoría: {{ $calificacion->juradoPresentacion?->presentacion?->inscripcion?->categoria?->categoria ?? '—' }} ·
                        Jurado: <strong>{{ $calificacion->juradoPresentacion?->usuario?->nombre }} {{ $calificacion->juradoPresentacion?->usuario?->paterno }}</strong>
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 uppercase block">Nota Actual Vigente</span>
                    <span class="text-2xl font-black text-slate-900 tabular-nums">{{ number_format($calificacion->nota_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Lista de Modificaciones del Historial -->
        <div class="space-y-4">
            <h3 class="text-base font-bold text-slate-800">Línea de Tiempo de Modificaciones</h3>

            @forelse ($calificacion->historiales as $index => $historial)
                @php
                    $datosAnteriores = $historial->datos_anteriores ?? [];
                    $datosNuevos = $historial->datos_nuevos ?? [];
                    $notaAnt = $datosAnteriores['nota_total'] ?? 0;
                    $notaNue = $datosNuevos['nota_total'] ?? 0;
                    $diff = $notaNue - $notaAnt;
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs">
                                #{{ $calificacion->historiales->count() - $index }}
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Modificado por {{ $historial->usuario?->nombre }} {{ $historial->usuario?->paterno }}
                                    <span class="text-xs font-normal text-slate-400">({{ $historial->usuario?->rol?->value ?? 'Usuario' }})</span>
                                </p>
                                <p class="text-xs text-slate-500">
                                    Fecha: {{ $historial->fecha?->format('d/m/Y H:i:s') ?? $historial->created_at?->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-start sm:self-auto">
                            <span class="text-xs text-slate-500">Nota jurado:</span>
                            <span class="font-bold text-slate-500 line-through text-xs">{{ number_format($notaAnt, 2) }}</span>
                            <span>→</span>
                            <span class="font-black text-slate-900 text-base">{{ number_format($notaNue, 2) }}</span>
                            <span class="px-2 py-0.5 text-xs font-bold rounded {{ $diff >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $diff >= 0 ? '+'.number_format($diff, 2) : number_format($diff, 2) }}
                            </span>
                        </div>
                    </div>

                    @if ($historial->motivo)
                        <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg text-xs text-amber-900">
                            <strong>Motivo registrado:</strong> {{ $historial->motivo }}
                        </div>
                    @endif

                    <!-- Comparación de parámetros -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-2">Parámetro</th>
                                    <th class="px-4 py-2 text-center">Valor Anterior</th>
                                    <th class="px-4 py-2 text-center">Nuevo Valor</th>
                                    <th class="px-4 py-2 text-right">Variación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $detAntMap = collect($datosAnteriores['detalles'] ?? [])->keyBy('id_parametro');
                                    $detNueMap = collect($datosNuevos['detalles'] ?? [])->keyBy('id_parametro');
                                    $allParams = $detAntMap->keys()->merge($detNueMap->keys())->unique();
                                @endphp

                                @foreach ($allParams as $idParam)
                                    @php
                                        $vAnt = (float) ($detAntMap->get($idParam)['valor'] ?? 0);
                                        $vNue = (float) ($detNueMap->get($idParam)['valor'] ?? 0);
                                        $vDiff = $vNue - $vAnt;
                                        $paramModel = App\Modules\Parametros\Models\Parametro::find($idParam);
                                    @endphp
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-2 font-medium text-slate-800">
                                            {{ $paramModel?->parametro ?? "Parámetro #{$idParam}" }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-slate-500 tabular-nums">
                                            {{ number_format($vAnt, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-center font-bold text-slate-900 tabular-nums">
                                            {{ number_format($vNue, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold tabular-nums {{ $vDiff > 0 ? 'text-emerald-600' : ($vDiff < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                            {{ $vDiff > 0 ? '+'.number_format($vDiff, 2) : ($vDiff < 0 ? number_format($vDiff, 2) : '=') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center text-slate-400 text-sm">
                    No se han registrado modificaciones para esta calificación.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
