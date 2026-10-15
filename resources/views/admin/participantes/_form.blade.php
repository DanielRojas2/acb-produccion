@php
    $participante ??= null;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-semibold text-slate-800 mb-4">
        {{ isset($participante) ? 'Editar participante' : 'Registrar participante' }}
    </h3>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input id="nombre" class="mt-1 block w-full" type="text" name="nombre"
                :value="old('nombre', $participante->nombre ?? null)" required autofocus />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="paterno" :value="__('Apellido paterno')" />
            <x-text-input id="paterno" class="mt-1 block w-full" type="text" name="paterno"
                :value="old('paterno', $participante->paterno ?? null)" />
            <x-input-error :messages="$errors->get('paterno')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="materno" :value="__('Apellido materno')" />
            <x-text-input id="materno" class="mt-1 block w-full" type="text" name="materno"
                :value="old('materno', $participante->materno ?? null)" />
            <x-input-error :messages="$errors->get('materno')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="pseudonimo" :value="__('Pseudónimo (opcional)')" />
            <x-text-input id="pseudonimo" class="mt-1 block w-full" type="text" name="pseudonimo"
                :value="old('pseudonimo', $participante->pseudonimo ?? null)" />
            <x-input-error :messages="$errors->get('pseudonimo')" class="mt-2" />
            <p class="mt-1 text-xs text-slate-400">Es el nombre artístico individual, distinto del nombre de presentación.</p>
        </div>

        <div class="md:col-span-2 border-t border-slate-100 pt-5 space-y-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Imágenes del participante</h4>

            <!-- 1. FOTO CON FONDO (RANKING PÚBLICO) -->
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <x-input-label for="foto" :value="__('1. Fotografía con fondo (para cards del ranking público)')" class="font-semibold text-slate-700" />
                    <span class="text-[11px] font-medium text-slate-400">Cards / Listado público</span>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    @if (isset($participante) && $participante->fotoUrl())
                        <div class="relative shrink-0">
                            <img src="{{ $participante->fotoUrl() }}" alt="{{ $participante->nombreCompleto() }}"
                                 class="h-20 w-20 rounded-xl object-cover border border-slate-300 shadow-sm bg-slate-200">
                        </div>
                    @endif

                    <div class="flex-1">
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-slate-300 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                        <x-input-error :messages="$errors->get('foto')" class="mt-1" />
                        <p class="mt-1 text-xs text-slate-400">Formatos: JPG, PNG, WEBP (máx. 4MB). Utilizada como fondo del card en el ranking público.</p>
                    </div>

                    @if (isset($participante) && $participante->foto)
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
                    @if (isset($participante) && $participante->fotoSinFondoUrl())
                        <div class="relative shrink-0 p-1.5 rounded-xl border border-amber-300 shadow-sm bg-gradient-to-b from-slate-900 to-black">
                            <img src="{{ $participante->fotoSinFondoUrl() }}" alt="{{ $participante->nombreCompleto() }}"
                                 class="h-20 w-20 object-contain drop-shadow-md">
                        </div>
                    @endif

                    <div class="flex-1">
                        <input type="file" id="foto_sin_fondo" name="foto_sin_fondo" accept="image/png"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:border file:border-amber-300 file:text-amber-900 hover:file:bg-amber-50 cursor-pointer" />
                        <x-input-error :messages="$errors->get('foto_sin_fondo')" class="mt-1" />
                        <p class="mt-1 text-xs text-amber-800/80">Obligatorio formato PNG con transparencia (máx. 4MB). Se proyecta en la mesa de calificación y pantalla de los jurados.</p>
                    </div>

                    @if (isset($participante) && $participante->foto_sin_fondo)
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

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('admin.participantes.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-md">
            Cancelar
        </a>
        <x-primary-button>
            {{ isset($participante) ? __('Guardar cambios') : __('Registrar participante') }}
        </x-primary-button>
    </div>
</div>