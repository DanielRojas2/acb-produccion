<?php

namespace App\Modules\Jurados\Controllers;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Jurados\Services\AsignarJuradosService;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JuradoController
{
    public function __construct(private readonly AsignarJuradosService $asignacion) {}

    public function index(Request $request): View
    {
        $categoriaSeleccionada = $request->string('categoria')->trim()->toString();
        $faseSeleccionada = $request->string('fase')->trim()->toString();
        $departamentoSeleccionado = $request->string('departamento')->trim()->toString();
        $sinCompletar = $request->boolean('sin_completar');

        $presentaciones = Presentacion::query()
            ->select('presentaciones.*')
            ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
            ->with([
                'inscripcion.categoria',
                'inscripcion.fase.departamento',
                'inscripcion.departamento',
                'inscripcion.participantes',
                'jurados.usuario',
            ])
            ->when($categoriaSeleccionada !== '', fn ($q) => $q->whereHas('inscripcion', fn ($i) => $i->whereHas('categoria', fn ($c) => $c->where('categoria', $categoriaSeleccionada))))
            ->when($faseSeleccionada === FaseEnum::PRESELECCION->value, fn ($q) => $q->whereHas('inscripcion', fn ($i) => $i->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::PRESELECCION->value))))
            ->when($faseSeleccionada === FaseEnum::FINAL->value, fn ($q) => $q->whereHas('inscripcion', fn ($i) => $i->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value))))
            ->when($departamentoSeleccionado !== '', fn ($q) => $q->whereHas('inscripcion', fn ($i) => $i->whereHas('departamento', fn ($d) => $d->where('nombre', $departamentoSeleccionado))))
            ->when($sinCompletar, function ($q) {
                $q->where(function ($q) {
                    $q->whereHas('inscripcion', fn ($i) => $i->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::PRESELECCION->value)))
                        ->whereHas('jurados', null, '<', 3);
                })->orWhere(function ($q) {
                    $q->whereHas('inscripcion', fn ($i) => $i->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value)))
                        ->whereHas('jurados', null, '<', 5);
                });
            })
            ->orderBy('inscripciones.fecha_presentacion')
            ->orderBy('inscripciones.hora_presentacion')
            ->orderBy('presentaciones.id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jurados.index', [
            'presentaciones' => $presentaciones,
            'jurados' => Usuario::where('rol', RolEnum::JURADO)->orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('categoria')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'faseSeleccionada' => $faseSeleccionada,
            'departamentoSeleccionado' => $departamentoSeleccionado,
            'sinCompletar' => $sinCompletar,
        ]);
    }

    public function asignar(Request $request): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $validados = $request->validate([
            'presentacion_id' => ['required', 'integer', 'exists:presentaciones,id'],
            'usuario_ids' => ['required', 'array'],
            'usuario_ids.*' => ['integer'],
            'secreto_usuario_id' => ['nullable', 'integer'],
        ]);

        $presentacion = Presentacion::findOrFail((int) $validados['presentacion_id']);
        $secretoUsuarioId = isset($validados['secreto_usuario_id']) && $validados['secreto_usuario_id'] !== '' ? (int) $validados['secreto_usuario_id'] : null;

        try {
            $this->asignacion->asignar($presentacion, $validados['usuario_ids'], $secretoUsuarioId);
        } catch (\UnexpectedValueException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf('Jurados de "%s" actualizados.', $presentacion->nombreMostrado()));
    }

    public function autoAsignar(Request $request): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $request->validate([
            'fase' => ['nullable', 'in:'.FaseEnum::PRESELECCION->value.','.FaseEnum::FINAL->value],
        ]);

        $fase = $request->string('fase')->trim()->toString();

        $presentaciones = Presentacion::query()
            ->where('estado', \App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value)
            ->when($fase !== '', fn ($q) => $q->whereHas('inscripcion', fn ($i) => $i->whereHas('fase', fn ($f) => $f->where('fase', $fase))));

        $procesadas = 0;
        $errores = [];

        foreach ($presentaciones->get() as $presentacion) {
            try {
                $nuevas = $this->asignacion->autoAsignar($presentacion);
                $procesadas += count($nuevas);
            } catch (\UnexpectedValueException $e) {
                $errores[$presentacion->id] = $e->getMessage();
            }
        }

        $mensaje = sprintf('Autoasignación completada: %d puestos cubiertos.', $procesadas);

        if ($errores !== []) {
            $mensaje .= ' '.implode(' | ', array_map(fn ($m) => "Presentación sin cupo: {$m}", array_slice($errores, 0, 3)));
        }

        return back()->with('status', $mensaje);
    }

    public function remover(Request $request): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $validados = $request->validate([
            'presentacion_id' => ['required', 'integer', 'exists:presentaciones,id'],
            'usuario_id' => ['required', 'integer'],
        ]);

        $presentacion = Presentacion::findOrFail((int) $validados['presentacion_id']);

        try {
            $this->asignacion->removerJurado($presentacion, (int) $validados['usuario_id']);
        } catch (\UnexpectedValueException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Jurado removido de la presentación.');
    }
}