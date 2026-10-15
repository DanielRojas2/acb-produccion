<?php

namespace App\Modules\Presentaciones\Controllers;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Presentaciones\Services\GestionarCronometroService;
use App\Modules\Presentaciones\Services\GestionarPresentacionService;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PresentacionController
{
    public function __construct(
        private readonly GestionarPresentacionService $presentaciones,
        private readonly GestionarCronometroService $cronometro,
    ) {}

    public function index(Request $request): View
    {
        $categoriaSeleccionada = $request->string('categoria')->trim()->toString();
        $faseSeleccionada = $request->string('fase')->trim()->toString();
        $departamentoSeleccionado = $request->string('departamento')->trim()->toString();
        $estadoSeleccionado = $request->string('estado')->trim()->toString();

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
            ->when($estadoSeleccionado !== '', fn ($q) => $q->where('presentaciones.estado', $estadoSeleccionado))
            ->orderBy('inscripciones.fecha_presentacion')
            ->orderBy('inscripciones.hora_presentacion')
            ->orderBy('presentaciones.id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.presentaciones.index', [
            'presentaciones' => $presentaciones,
            'categorias' => Categoria::orderBy('categoria')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'faseSeleccionada' => $faseSeleccionada,
            'departamentoSeleccionado' => $departamentoSeleccionado,
            'estadoSeleccionado' => $estadoSeleccionado,
            'estados' => PresentacionEstadoEnum::cases(),
        ]);
    }

    /**
     * Vista de escenario: la presentación en curso (o la siguiente) con
     * su nombre y el cronómetro proyectable. Compartida entre admin y jurado.
     */
    public function escenario(Request $request): View
    {
        $presentacionId = $request->integer('presentacion_id') ?: $request->integer('p');
        $actual = null;
        $siguiente = null;

        if ($presentacionId) {
            $actual = Presentacion::query()
                ->with(['inscripcion.categoria', 'inscripcion.participantes', 'jurados.usuario'])
                ->find($presentacionId);
        }

        if ($actual === null) {
            [$actual, $siguiente] = $this->presentacionEnCurso();
        } else {
            $siguiente = Presentacion::query()
                ->select('presentaciones.*')
                ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
                ->with(['inscripcion.categoria', 'inscripcion.participantes'])
                ->where('presentaciones.estado', PresentacionEstadoEnum::POR_PARTICIPAR->value)
                ->where('presentaciones.id', '!=', $actual->id)
                ->orderBy('inscripciones.fecha_presentacion')
                ->orderBy('inscripciones.hora_presentacion')
                ->orderBy('presentaciones.id')
                ->first();
        }

        $usuario = $request->user();
        $juradoPresentacion = null;
        $calificacion = null;
        $categoriaParametros = collect();
        $puedeControlar = false;
        $juradosAsignados = collect();

        if ($actual !== null) {
            $categoriaParametros = $actual->inscripcion?->categoria?->categoriaParametros()
                ->with('parametro')
                ->orderBy('orden')
                ->get() ?? collect();

            if ($usuario) {
                $juradoPresentacion = $actual->jurados()
                    ->where('id_usuario', $usuario->id)
                    ->first();

                if ($juradoPresentacion) {
                    $calificacion = $juradoPresentacion->calificacion()
                        ->with('detalles.parametro')
                        ->first();
                }

                $puedeControlar = Gate::allows('controlarCronometro', $actual);
            }

            $juradosAsignados = $actual->jurados()
                ->with(['usuario', 'calificacion.detalles'])
                ->get();
        }

        return view('jurado.escenario', [
            'presentacion' => $actual,
            'siguiente' => $siguiente,
            'cronometro' => $actual !== null ? $this->cronometro->obtenerEstado($actual) : null,
            'categoriaParametros' => $categoriaParametros,
            'juradoPresentacion' => $juradoPresentacion,
            'calificacion' => $calificacion,
            'puedeControlar' => $puedeControlar,
            'juradosAsignados' => $juradosAsignados,
        ]);
    }

    public function transicion(Request $request, Presentacion $presentacion): RedirectResponse
    {
        $estado = PresentacionEstadoEnum::tryFrom($request->string('estado')->toString());

        if ($estado === null) {
            return back()->with('error', 'Estado de destino no válido.');
        }

        try {
            $presentacion = match ($estado) {
                PresentacionEstadoEnum::EN_ESCENARIO => $this->presentaciones->alEscenario($presentacion),
                PresentacionEstadoEnum::PARTICIPANDO => $this->presentaciones->iniciarPresentacion($presentacion),
                PresentacionEstadoEnum::FINALIZADO => $this->presentaciones->finalizarPresentacion($presentacion),
                default => throw new \UnexpectedValueException('Estado no controlable.'),
            };
        } catch (\UnexpectedValueException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf('Presentación marcada como "%s".', $presentacion->estado?->label()));
    }

    public function cronometroIniciar(Request $request, Presentacion $presentacion): RedirectResponse|JsonResponse
    {
        Gate::authorize('controlarCronometro', $presentacion);

        try {
            if ($presentacion->estado === PresentacionEstadoEnum::EN_ESCENARIO) {
                $this->presentaciones->iniciarPresentacion($presentacion);
            } else {
                $this->cronometro->iniciar($presentacion);
            }
        } catch (\UnexpectedValueException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cronometro' => $this->cronometro->obtenerEstado($presentacion),
                'estado' => $presentacion->estado?->value,
                'estado_label' => $presentacion->estado?->label(),
            ]);
        }

        return back()->with('status', 'Cronómetro iniciado.');
    }

    public function cronometroDetener(Request $request, Presentacion $presentacion): RedirectResponse|JsonResponse
    {
        Gate::authorize('controlarCronometro', $presentacion);

        try {
            if ($presentacion->estado === PresentacionEstadoEnum::PARTICIPANDO) {
                $this->presentaciones->finalizarPresentacion($presentacion);
            } else {
                $this->cronometro->detener($presentacion);
            }
        } catch (\UnexpectedValueException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cronometro' => $this->cronometro->obtenerEstado($presentacion),
                'estado' => $presentacion->estado?->value,
                'estado_label' => $presentacion->estado?->label(),
            ]);
        }

        return back()->with('status', 'Cronómetro detenido.');
    }

    /**
     * @return array{0: Presentacion|null, 1: Presentacion|null}
     */
    private function presentacionEnCurso(): array
    {
        $actual = Presentacion::query()
            ->with(['inscripcion.categoria', 'inscripcion.participantes', 'jurados.usuario'])
            ->whereIn('estado', [
                PresentacionEstadoEnum::EN_ESCENARIO->value,
                PresentacionEstadoEnum::PARTICIPANDO->value,
            ])
            ->orderByDesc('updated_at')
            ->first();

        if ($actual === null) {
            $actual = Presentacion::query()
                ->with(['inscripcion.categoria', 'inscripcion.participantes', 'jurados.usuario'])
                ->where('estado', PresentacionEstadoEnum::FINALIZADO->value)
                ->orderByDesc('updated_at')
                ->first();
        }

        $siguiente = Presentacion::query()
            ->select('presentaciones.*')
            ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
            ->with(['inscripcion.categoria', 'inscripcion.participantes'])
            ->where('presentaciones.estado', PresentacionEstadoEnum::POR_PARTICIPAR->value)
            ->orderBy('inscripciones.fecha_presentacion')
            ->orderBy('inscripciones.hora_presentacion')
            ->orderBy('presentaciones.id')
            ->first();

        return [$actual, $siguiente];
    }
}