@php
    $inscripcion ??= null;
    $categorias ??= collect();
    $departamentos ??= collect();

    $inscripcionCategoria = old('categoria', $inscripcion?->categoria?->categoria ?? '');
    $inscripcionFase = strtoupper(old('fase', $inscripcion?->fase?->fase?->value ?? App\Support\Enums\FaseEnum::PRESELECCION->value));
    $inscripcionDepartamento = old('departamento', $inscripcion?->departamento?->nombre ?? '');

    $integrantesPrevios = old('integrantes');

    if ($integrantesPrevios === null) {
        $integrantesPrevios = $inscripcion !== null
            ? $inscripcion->participantes
                ->map(fn ($p) => ['nombre' => $p->nombre, 'paterno' => $p->paterno, 'materno' => $p->materno, 'pseudonimo' => $p->pseudonimo])
                ->all()
            : [[]];
    }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-semibold text-slate-800 mb-4">
        {{ isset($inscripcion) ? 'Editar inscripción' : 'Registrar inscripción' }}
    </h3>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="categoria" :value="__('Categoría')" />
            <select id="categoria" name="categoria" required autofocus
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Seleccione una categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->categoria }}" data-max="{{ $categoria->max_participantes }}"
                        @selected(old('categoria', $inscripcionCategoria) === $categoria->categoria)>
                        {{ $categoria->categoria }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('categoria')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="fase" :value="__('Fase')" />
            <select id="fase" name="fase" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="{{ App\Support\Enums\FaseEnum::PRESELECCION->value }}" @selected($inscripcionFase === App\Support\Enums\FaseEnum::PRESELECCION->value)>
                    {{ App\Support\Enums\FaseEnum::PRESELECCION->label() }}
                </option>
                <option value="{{ App\Support\Enums\FaseEnum::FINAL->value }}" @selected($inscripcionFase === App\Support\Enums\FaseEnum::FINAL->value)>
                    {{ App\Support\Enums\FaseEnum::FINAL->label() }}
                </option>
            </select>
            <x-input-error :messages="$errors->get('fase')" class="mt-2" />
        </div>

        <div id="campo-departamento">
            <x-input-label for="departamento" :value="__('Departamento / Delegación')" />
            <select id="departamento" name="departamento"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Seleccione un departamento</option>
                @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento->nombre }}" @selected(old('departamento', $inscripcionDepartamento) === $departamento->nombre)>
                        {{ $departamento->nombre }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('departamento')" class="mt-2" />
            <p class="mt-1 text-xs text-slate-400">
                Delegación departamental a la que representa (obligatorio en Preselección, representativo en Fase Final).
            </p>
        </div>

        <div>
            <x-input-label for="fecha_inscripcion" :value="__('Fecha de inscripción')" />
            <input id="fecha_inscripcion" type="date" name="fecha_inscripcion"
                value="{{ old('fecha_inscripcion', $inscripcion?->fecha_inscripcion?->toDateString() ?? now()->toDateString()) }}"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <x-input-error :messages="$errors->get('fecha_inscripcion')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="fecha_presentacion" :value="__('Fecha de presentación (opcional)')" />
            <input id="fecha_presentacion" type="date" name="fecha_presentacion"
                value="{{ old('fecha_presentacion', $inscripcion?->fecha_presentacion?->toDateString() ?? null) }}"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <x-input-error :messages="$errors->get('fecha_presentacion')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="hora_presentacion" :value="__('Hora de presentación (opcional)')" />
            <input id="hora_presentacion" type="time" name="hora_presentacion"
                value="{{ old('hora_presentacion', $inscripcion?->hora_presentacion?->format('H:i') ?? null) }}"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <x-input-error :messages="$errors->get('hora_presentacion')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="nombre_presentacion" :value="__('Nombre de presentación (opcional)')" />
            <x-text-input id="nombre_presentacion" class="mt-1 block w-full" type="text" name="nombre_presentacion"
                :value="old('nombre_presentacion', $inscripcion?->presentacion?->nombre_presentacion ?? null)" />
            <x-input-error :messages="$errors->get('nombre_presentacion')" class="mt-2" />
            <p class="mt-1 text-xs text-slate-400">
                Si se deja vacío, se usará el nombre completo del primer integrante.
            </p>
        </div>

        <div class="md:col-span-2 border-t border-slate-100 pt-5 space-y-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Imágenes de la presentación</h4>

            <!-- 1. FOTO CON FONDO (RANKING PÚBLICO) -->
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <x-input-label for="foto" :value="__('1. Fotografía con fondo (para cards del ranking público)')" class="font-semibold text-slate-700" />
                    <span class="text-[11px] font-medium text-slate-400">Cards / Listado público</span>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    @php
                        $fotoActualUrl = $inscripcion?->presentacion?->fotoUrl() ?? $inscripcion?->participantes->first()?->fotoUrl();
                        $tieneFoto = (bool) ($inscripcion?->presentacion?->foto ?? $inscripcion?->participantes->first()?->foto);
                    @endphp

                    @if ($fotoActualUrl)
                        <div class="relative shrink-0">
                            <img src="{{ $fotoActualUrl }}" alt="Foto con fondo actual"
                                 class="h-20 w-20 rounded-xl object-cover border border-slate-300 shadow-sm bg-slate-200">
                        </div>
                    @endif

                    <div class="flex-1">
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-slate-300 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                        <x-input-error :messages="$errors->get('foto')" class="mt-1" />
                        <p class="mt-1 text-xs text-slate-400">Formatos: JPG, PNG, WEBP (máx. 4MB). Utilizada como fondo del card en el ranking público.</p>
                    </div>

                    @if ($tieneFoto)
                        <div class="flex items-center gap-2 mt-2 sm:mt-0">
                            <label class="inline-flex items-center text-xs text-rose-600 font-semibold cursor-pointer">
                                <input type="checkbox" name="eliminar_foto" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 mr-1.5">
                                Eliminar foto con fondo
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. IMAGEN SIN FONDO EN PNG (VISTA DEL JURADO) -->
            <div class="p-4 rounded-xl bg-amber-50/40 border border-amber-200/80">
                <div class="flex items-center justify-between mb-2">
                    <x-input-label for="foto_sin_fondo" :value="__('2. Imagen para el jurado (sin fondo en PNG)')" class="font-semibold text-amber-900" />
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider bg-amber-100/80 px-2 py-0.5 rounded">PNG Transparente</span>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    @php
                        $fotoSinFondoActualUrl = $inscripcion?->presentacion?->fotoSinFondoUrl() ?? $inscripcion?->participantes->first()?->fotoSinFondoUrl();
                        $tieneFotoSinFondo = (bool) ($inscripcion?->presentacion?->foto_sin_fondo ?? $inscripcion?->participantes->first()?->foto_sin_fondo);
                    @endphp

                    @if ($fotoSinFondoActualUrl)
                        <div class="relative shrink-0 p-1.5 rounded-xl border border-amber-300 shadow-sm bg-gradient-to-b from-slate-900 to-black">
                            <img src="{{ $fotoSinFondoActualUrl }}" alt="Imagen para jurado actual"
                                 class="h-20 w-20 object-contain drop-shadow-md">
                        </div>
                    @endif

                    <div class="flex-1">
                        <input type="file" id="foto_sin_fondo" name="foto_sin_fondo" accept="image/png"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-amber-300 file:text-amber-900 hover:file:bg-amber-50 cursor-pointer" />
                        <x-input-error :messages="$errors->get('foto_sin_fondo')" class="mt-1" />
                        <p class="mt-1 text-xs text-amber-800/80">Obligatorio formato PNG con transparencia (máx. 4MB). Se proyecta en la mesa de calificación y pantalla de los jurados.</p>
                    </div>

                    @if ($tieneFotoSinFondo)
                        <div class="flex items-center gap-2 mt-2 sm:mt-0">
                            <label class="inline-flex items-center text-xs text-rose-600 font-semibold cursor-pointer">
                                <input type="checkbox" name="eliminar_foto_sin_fondo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 mr-1.5">
                                Eliminar imagen sin fondo
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="flex items-center justify-between">
            <x-input-label :value="__('Integrantes')" />
            <button type="button" id="agregar-integrante"
                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100">
                + Agregar integrante
            </button>
        </div>
        <p id="max-integrante" class="mt-1 text-xs text-slate-400"></p>

        <div id="integrantes" class="mt-3 space-y-3">
            <template id="integrantes-fila">
                <div class="fila-integrante grid gap-2 md:grid-cols-[1fr_1fr_1fr_1fr_auto] items-end">
                    <input type="text" name="integrantes[--INDEX--][nombre]" placeholder="Nombre"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[--INDEX--][paterno]" placeholder="Apellido paterno"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[--INDEX--][materno]" placeholder="Apellido materno"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[--INDEX--][pseudonimo]" placeholder="Pseudónimo"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <button type="button" data-eliminar
                        class="px-3 py-2 text-sm text-red-600 hover:text-red-800">
                        Quitar
                    </button>
                </div>
            </template>

            @foreach ($integrantesPrevios as $indice => $integrante)
                <div class="fila-integrante grid gap-2 md:grid-cols-[1fr_1fr_1fr_1fr_auto] items-end">
                    <input type="text" name="integrantes[{{ $indice }}][nombre]" placeholder="Nombre" value="{{ $integrante['nombre'] ?? '' }}"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[{{ $indice }}][paterno]" placeholder="Apellido paterno" value="{{ $integrante['paterno'] ?? '' }}"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[{{ $indice }}][materno]" placeholder="Apellido materno" value="{{ $integrante['materno'] ?? '' }}"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" name="integrantes[{{ $indice }}][pseudonimo]" placeholder="Pseudónimo" value="{{ $integrante['pseudonimo'] ?? '' }}"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <button type="button" data-eliminar
                        class="px-3 py-2 text-sm text-red-600 hover:text-red-800">
                        Quitar
                    </button>
                </div>
            @endforeach
        </div>

        <x-input-error :messages="$errors->get('integrantes')" class="mt-2" />
    </div>

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('admin.inscripciones.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-md">
            Cancelar
        </a>
        <x-primary-button>
            {{ isset($inscripcion) ? __('Guardar cambios') : __('Registrar inscripción') }}
        </x-primary-button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var contenedor = document.getElementById('integrantes');
        var plantilla = document.getElementById('integrantes-fila');
        var agregar = document.getElementById('agregar-integrante');
        var indice = contenedor.querySelectorAll('.fila-integrante').length;

        function nuevaFila() {
            if (!plantilla || !contenedor) return;
            var nodo = plantilla.content.cloneNode(true);
            nodo.querySelectorAll('[name]').forEach(function (campo) {
                campo.setAttribute('name', campo.getAttribute('name').replace('--INDEX--', indice));
            });
            indice++;
            contenedor.appendChild(nodo);
        }

        agregar.addEventListener('click', nuevaFila);

        contenedor.addEventListener('click', function (event) {
            var boton = event.target.closest('[data-eliminar]');
            if (!boton) return;
            var filas = contenedor.querySelectorAll('.fila-integrante');
            if (filas.length > 1) {
                boton.closest('.fila-integrante').remove();
            }
        });

        var categoria = document.getElementById('categoria');
        var maxHint = document.getElementById('max-integrante');

        function actualizarMax() {
            var opcion = categoria.selectedOptions[0];
            var max = opcion ? opcion.dataset.max : 1;
            maxHint.textContent = 'Máximo ' + max + ' integrante(s) por presentación.';
        }

        categoria.addEventListener('change', actualizarMax);
        actualizarMax();
    });
</script>