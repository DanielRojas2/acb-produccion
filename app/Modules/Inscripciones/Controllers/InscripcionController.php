<?php

namespace App\Modules\Inscripciones\Controllers;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Inscripciones\Services\RegistrarInscripcionService;
use App\Support\Enums\FaseEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InscripcionController
{
    public function __construct(private readonly RegistrarInscripcionService $registro) {}

    public function index(Request $request): View
    {
        $categoriaSeleccionada = $request->string('categoria')->trim()->toString();
        $faseSeleccionada = $request->string('fase')->trim()->toString();
        $departamentoSeleccionado = $request->string('departamento')->trim()->toString();
        $busqueda = $request->string('busqueda')->trim()->toString();

        $inscripciones = Inscripcion::query()
            ->with(['categoria', 'fase.departamento', 'departamento', 'presentacion', 'participantes'])
            ->when($categoriaSeleccionada !== '', fn ($q) => $q->whereHas('categoria', fn ($c) => $c->where('categoria', $categoriaSeleccionada)))
            ->when($faseSeleccionada === FaseEnum::PRESELECCION->value, fn ($q) => $q->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::PRESELECCION->value)))
            ->when($faseSeleccionada === FaseEnum::FINAL->value, fn ($q) => $q->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value)))
            ->when($departamentoSeleccionado !== '', fn ($q) => $q->whereHas('departamento', fn ($d) => $d->where('nombre', $departamentoSeleccionado)))
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($q) use ($busqueda) {
                    $q->whereHas('presentacion', fn ($p) => $p->where('nombre_presentacion', 'like', "%{$busqueda}%"))
                        ->orWhereHas('participantes', fn ($p) => $p->where('nombre', 'like', "%{$busqueda}%")
                            ->orWhere('paterno', 'like', "%{$busqueda}%")
                            ->orWhere('materno', 'like', "%{$busqueda}%")
                            ->orWhere('pseudonimo', 'like', "%{$busqueda}%"));
                });
            })
            ->orderByDesc('fecha_inscripcion')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inscripciones.index', [
            'inscripciones' => $inscripciones,
            'categorias' => Categoria::orderBy('categoria')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'faseSeleccionada' => $faseSeleccionada,
            'departamentoSeleccionado' => $departamentoSeleccionado,
            'busqueda' => $busqueda,
        ]);
    }

    public function create(): View
    {
        return view('admin.inscripciones.create', $this->opciones());
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['nullable', 'image', 'max:4096'],
            'foto_sin_fondo' => ['nullable', 'image', 'mimes:png', 'max:4096'],
        ], [
            'foto_sin_fondo.mimes' => 'La imagen para el jurado debe ser una imagen sin fondo en formato PNG.',
        ]);

        $datos = $this->datosFormulario($request);

        $this->comprobarValidos($datos);

        $inscripcion = $this->registro->registrar($datos);

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('participantes', 'public');
            $inscripcion->presentacion?->forceFill(['foto' => $fotoPath])->save();
            $inscripcion->participantes->first()?->forceFill(['foto' => $fotoPath])->save();
        }

        if ($request->hasFile('foto_sin_fondo')) {
            $fotoSinFondoPath = $request->file('foto_sin_fondo')->store('participantes', 'public');
            $inscripcion->presentacion?->forceFill(['foto_sin_fondo' => $fotoSinFondoPath])->save();
            $inscripcion->participantes->first()?->forceFill(['foto_sin_fondo' => $fotoSinFondoPath])->save();
        }

        return redirect()
            ->route('admin.inscripciones.index')
            ->with('status', 'Inscripción registrada correctamente.');
    }

    public function edit(Inscripcion $inscripcion): View
    {
        $this->comprobarEditable($inscripcion);

        return view('admin.inscripciones.edit', [
            ...$this->opciones(),
            'inscripcion' => $inscripcion->load(['fase.departamento', 'departamento', 'presentacion', 'participantes']),
        ]);
    }

    public function update(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->comprobarEditable($inscripcion);

        $request->validate([
            'foto' => ['nullable', 'image', 'max:4096'],
            'foto_sin_fondo' => ['nullable', 'image', 'mimes:png', 'max:4096'],
        ], [
            'foto_sin_fondo.mimes' => 'La imagen para el jurado debe ser una imagen sin fondo en formato PNG.',
        ]);

        $datos = $this->datosFormulario($request);

        $this->comprobarValidos($datos);

        $this->registro->actualizar($inscripcion, $datos);

        if ($request->boolean('eliminar_foto')) {
            if ($inscripcion->presentacion?->foto && ! str_starts_with($inscripcion->presentacion->foto, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($inscripcion->presentacion->foto);
            }
            $inscripcion->presentacion?->forceFill(['foto' => null])->save();
            foreach ($inscripcion->participantes as $p) {
                if ($p->foto && ! str_starts_with($p->foto, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($p->foto);
                }
                $p->forceFill(['foto' => null])->save();
            }
        } elseif ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('participantes', 'public');
            $inscripcion->presentacion?->forceFill(['foto' => $fotoPath])->save();
            $inscripcion->participantes->first()?->forceFill(['foto' => $fotoPath])->save();
        }

        if ($request->boolean('eliminar_foto_sin_fondo')) {
            if ($inscripcion->presentacion?->foto_sin_fondo && ! str_starts_with($inscripcion->presentacion->foto_sin_fondo, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($inscripcion->presentacion->foto_sin_fondo);
            }
            $inscripcion->presentacion?->forceFill(['foto_sin_fondo' => null])->save();
            foreach ($inscripcion->participantes as $p) {
                if ($p->foto_sin_fondo && ! str_starts_with($p->foto_sin_fondo, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($p->foto_sin_fondo);
                }
                $p->forceFill(['foto_sin_fondo' => null])->save();
            }
        } elseif ($request->hasFile('foto_sin_fondo')) {
            $fotoSinFondoPath = $request->file('foto_sin_fondo')->store('participantes', 'public');
            $inscripcion->presentacion?->forceFill(['foto_sin_fondo' => $fotoSinFondoPath])->save();
            $inscripcion->participantes->first()?->forceFill(['foto_sin_fondo' => $fotoSinFondoPath])->save();
        }

        return redirect()
            ->route('admin.inscripciones.index')
            ->with('status', 'Inscripción actualizada correctamente.');
    }

    public function destroy(Inscripcion $inscripcion): RedirectResponse
    {
        $this->comprobarEditable($inscripcion);

        if ($inscripcion->presentacion?->foto && ! str_starts_with($inscripcion->presentacion->foto, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($inscripcion->presentacion->foto);
        }
        if ($inscripcion->presentacion?->foto_sin_fondo && ! str_starts_with($inscripcion->presentacion->foto_sin_fondo, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($inscripcion->presentacion->foto_sin_fondo);
        }

        $inscripcion->delete();

        return redirect()
            ->route('admin.inscripciones.index')
            ->with('status', 'Inscripción eliminada.');
    }

    /**
     * @return array{categorias:list<Categoria>, departamentos:list<Departamento>}
     */
    private function opciones(): array
    {
        return [
            'categorias' => Categoria::orderBy('categoria')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function datosFormulario(Request $request): array
    {
        $integrantes = [];

        foreach ($request->input('integrantes', []) as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $integrante = [
                'nombre' => $this->limpiar($fila['nombre'] ?? null),
                'paterno' => $this->limpiar($fila['paterno'] ?? null),
                'materno' => $this->limpiar($fila['materno'] ?? null),
                'pseudonimo' => $this->limpiar($fila['pseudonimo'] ?? null),
            ];

            if (! $this->integranteVacio($integrante)) {
                $integrantes[] = $integrante;
            }
        }

        return [
            'categoria' => $this->limpiar($request->string('categoria')->toString()),
            'departamento' => $this->limpiar($request->string('departamento')->toString()),
            'fase' => $this->limpiar($request->string('fase')->toString()),
            'fecha_inscripcion' => $this->limpiar($request->string('fecha_inscripcion')->toString()),
            'fecha_presentacion' => $this->limpiar($request->string('fecha_presentacion')->toString()),
            'hora_presentacion' => $this->limpiar($request->string('hora_presentacion')->toString()),
            'nombre_presentacion' => $this->limpiar($request->string('nombre_presentacion')->toString()),
            'integrantes' => $integrantes,
        ];
    }

    private function limpiar(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    /**
     * @param  array{nombre:?string,paterno:?string,materno:?string,pseudonimo:?string}  $integrante
     */
    private function integranteVacio(array $integrante): bool
    {
        return $integrante['nombre'] === null
            && $integrante['paterno'] === null
            && $integrante['materno'] === null
            && $integrante['pseudonimo'] === null;
    }

    /**
     * @param  array<string,mixed>  $datos
     *
     * @throws ValidationException
     */
    private function comprobarValidos(array $datos): void
    {
        $errores = $this->registro->validar($datos);

        if ($errores === []) {
            return;
        }

        $mensajes = [];

        foreach ($errores as $error) {
            $campo = match (true) {
                str_starts_with($error, 'Falta la categoría') || str_contains($error, 'no existe (verifique el nombre oficial') => 'categoria',
                str_starts_with($error, 'La fase') => 'fase',
                str_starts_with($error, 'En preselección'), str_starts_with($error, 'El departamento') => 'departamento',
                str_starts_with($error, 'La fecha de inscripción') => 'fecha_inscripcion',
                str_starts_with($error, 'La fecha de presentación') => 'fecha_presentacion',
                str_starts_with($error, 'La hora') => 'hora_presentacion',
                default => 'integrantes',
            };

            $mensajes[$campo][] = $error;
        }

        throw ValidationException::withMessages($mensajes);
    }

    private function comprobarEditable(Inscripcion $inscripcion): void
    {
        abort_if(! $inscripcion->puedeAdministrarse(), 403, 'La presentación ya está en uso; no puede modificarse ni eliminarse.');
    }
}
