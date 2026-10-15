<x-layouts.admin>
    <x-slot:header>
        <h2 class="text-xl font-semibold text-slate-800">Importaciones</h2>
    </x-slot:header>

    <x-slot:sidebar>
        <x-menus.admin />
    </x-slot:sidebar>

    <div class="space-y-6">
        @if (isset($resultado))
            @if ($resultado instanceof \App\Modules\Importaciones\ValueObjects\ResultadoImportacion && $resultado->tieneErrores())
                <div class="rounded-xl border border-red-200 bg-red-50 p-6">
                    <h3 class="font-semibold text-red-800">
                        {{ $resultado->errorGeneral !== null ? 'No se pudo importar' : 'La importación no se guardó (hay errores)' }}
                    </h3>
                    <p class="mt-1 text-sm text-red-700">
                        Se validaron {{ $resultado->filasProcesadas }} fila(s). Corrija los errores y vuelva a intentar; no se guardó ningún dato.
                    </p>

                    @if ($resultado->errorGeneral !== null)
                        <p class="mt-3 text-sm font-medium text-red-800">{{ $resultado->errorGeneral }}</p>
                    @endif

                    @if ($resultado->errores !== [])
                        <div class="mt-4 max-h-72 overflow-auto rounded-lg border border-red-200 bg-white">
                            <table class="w-full text-sm">
                                <thead class="bg-red-100 text-left text-xs uppercase text-red-900">
                                    <tr>
                                        <th class="px-4 py-2 w-16">Fila</th>
                                        <th class="px-4 py-2">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-red-100">
                                    @foreach ($resultado->errores as $error)
                                        <tr>
                                            <td class="px-4 py-2 font-semibold text-red-800">#{{ $error['fila'] }}</td>
                                            <td class="px-4 py-2 text-red-700">{{ $error['mensaje'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @elseif (isset($resultado) && $resultado->exitoso)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6">
                    <h3 class="font-semibold text-emerald-800">Importación exitosa</h3>
                    <p class="mt-1 text-sm text-emerald-700">
                        Se procesaron {{ $resultado->filasProcesadas }} fila(s) correctamente.
                    </p>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-lg bg-white p-4 text-center shadow-sm">
                            <dt class="text-xs uppercase text-slate-500">Inscripciones</dt>
                            <dd class="text-2xl font-bold text-emerald-700">{{ $resultado->inscripcionesCreadas }}</dd>
                        </div>
                        <div class="rounded-lg bg-white p-4 text-center shadow-sm">
                            <dt class="text-xs uppercase text-slate-500">Participantes nuevos</dt>
                            <dd class="text-2xl font-bold text-emerald-700">{{ $resultado->participantesCreados }}</dd>
                        </div>
                        <div class="rounded-lg bg-white p-4 text-center shadow-sm">
                            <dt class="text-xs uppercase text-slate-500">Presentaciones</dt>
                            <dd class="text-2xl font-bold text-emerald-700">{{ $resultado->presentacionesCreadas }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Importar inscripciones</h3>
                    <p class="mt-1 text-sm text-slate-600 max-w-2xl">
                        Suba un archivo <strong>CSV, JSON o XLSX</strong> con una fila por
                        inscripción. Cada fila indica la categoría, fase, departamento (en
                        preselección), fechas opcionales y los integrantes en columnas
                        <code class="text-xs bg-slate-100 px-1 rounded">integrante_1_nombre</code>,
                        <code class="text-xs bg-slate-100 px-1 rounded">integrante_1_paterno</code>,
                        <code class="text-xs bg-slate-100 px-1 rounded">integrante_1_materno</code>,
                        <code class="text-xs bg-slate-100 px-1 rounded">integrante_1_pseudonimo</code>, etc.
                        El sistema valida todo el archivo antes de guardar: si hay errores no se
                        persiste nada.
                    </p>
                </div>
                <a href="{{ route('admin.importaciones.plantilla') }}" class="shrink-0 inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100">
                    Descargar plantilla CSV
                </a>
            </div>

            <form method="POST" action="{{ route('admin.importaciones.importar') }}" enctype="multipart/form-data" class="mt-6 flex flex-col items-start gap-4 sm:flex-row sm:items-end">
                @csrf

                <div class="w-full sm:max-w-sm">
                    <x-input-label for="archivo" value="Archivo de importación" />
                    <input id="archivo" type="file" name="archivo" accept=".csv,.json,.xlsx"
                        class="mt-1 block w-full text-sm text-slate-700 border border-slate-300 rounded-lg p-2 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-white file:text-xs file:font-semibold hover:file:bg-indigo-700" />
                    <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                </div>

                <x-primary-button>
                    Importar
                </x-primary-button>
            </form>
        </div>
    </div>
</x-layouts.admin>