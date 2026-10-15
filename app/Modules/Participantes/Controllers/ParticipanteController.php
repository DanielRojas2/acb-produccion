<?php

namespace App\Modules\Participantes\Controllers;

use App\Modules\Participantes\Models\Participante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParticipanteController
{
    public function index(Request $request): View
    {
        $busqueda = $request->string('busqueda')->trim()->toString();

        $participantes = Participante::query()
            ->withCount('inscripciones')
            ->when($busqueda !== '', fn ($q) => $q->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('paterno', 'like', "%{$busqueda}%")
                    ->orWhere('materno', 'like', "%{$busqueda}%")
                    ->orWhere('pseudonimo', 'like', "%{$busqueda}%");
            }))
            ->orderBy('paterno')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.participantes.index', [
            'participantes' => $participantes,
            'busqueda' => $busqueda,
        ]);
    }

    public function create(): View
    {
        return view('admin.participantes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $this->comprobarDuplicado($datos, null);

        if ($request->hasFile('foto')) {
            $datos['foto'] = $request->file('foto')->store('participantes', 'public');
        }

        if ($request->hasFile('foto_sin_fondo')) {
            $datos['foto_sin_fondo'] = $request->file('foto_sin_fondo')->store('participantes', 'public');
        }

        Participante::create($datos);

        return redirect()
            ->route('admin.participantes.index')
            ->with('status', 'Participante registrado correctamente.');
    }

    public function edit(Participante $participante): View
    {
        return view('admin.participantes.edit', [
            'participante' => $participante->load('inscripciones.categoria', 'inscripciones.fase', 'inscripciones.departamento'),
        ]);
    }

    public function update(Request $request, Participante $participante): RedirectResponse
    {
        $datos = $this->validar($request);

        $this->comprobarDuplicado($datos, $participante->id);

        if ($request->boolean('eliminar_foto')) {
            if ($participante->foto && ! str_starts_with($participante->foto, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto);
            }
            $datos['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            if ($participante->foto && ! str_starts_with($participante->foto, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto);
            }
            $datos['foto'] = $request->file('foto')->store('participantes', 'public');
        }

        if ($request->boolean('eliminar_foto_sin_fondo')) {
            if ($participante->foto_sin_fondo && ! str_starts_with($participante->foto_sin_fondo, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto_sin_fondo);
            }
            $datos['foto_sin_fondo'] = null;
        } elseif ($request->hasFile('foto_sin_fondo')) {
            if ($participante->foto_sin_fondo && ! str_starts_with($participante->foto_sin_fondo, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto_sin_fondo);
            }
            $datos['foto_sin_fondo'] = $request->file('foto_sin_fondo')->store('participantes', 'public');
        }

        $participante->fill($datos)->save();

        return redirect()
            ->route('admin.participantes.index')
            ->with('status', 'Participante actualizado correctamente.');
    }

    public function destroy(Participante $participante): RedirectResponse
    {
        abort_if($participante->inscripciones()->exists(), 403, 'No se puede eliminar un participante que ya figura en inscripciones.');

        if ($participante->foto && ! str_starts_with($participante->foto, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto);
        }

        if ($participante->foto_sin_fondo && ! str_starts_with($participante->foto_sin_fondo, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($participante->foto_sin_fondo);
        }

        $participante->delete();

        return redirect()
            ->route('admin.participantes.index')
            ->with('status', 'Participante eliminado.');
    }

    /**
     * @throws ValidationException
     */
    protected function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'paterno' => ['nullable', 'string', 'max:255'],
            'materno' => ['nullable', 'string', 'max:255'],
            'pseudonimo' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'foto_sin_fondo' => ['nullable', 'image', 'mimes:png', 'max:4096'],
        ], [
            'foto_sin_fondo.mimes' => 'La imagen para el jurado debe ser una imagen sin fondo en formato PNG.',
        ]);
    }

    /**
     * @param  array{nombre:string,paterno:?string,materno:?string,pseudonimo:?string}  $datos
     *
     * @throws ValidationException
     */
    protected function comprobarDuplicado(array $datos, ?int $ignorarId): void
    {
        $existente = Participante::query()
            ->where('nombre', $datos['nombre'])
            ->where('paterno', $datos['paterno'] ?? null)
            ->where('materno', $datos['materno'] ?? null)
            ->when($ignorarId !== null, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->first();

        if ($existente !== null) {
            throw ValidationException::withMessages([
                'nombre' => sprintf('Ya existe un participante con estos datos (id %d): %s.', $existente->id, $existente->nombreCompleto()),
            ]);
        }
    }
}
