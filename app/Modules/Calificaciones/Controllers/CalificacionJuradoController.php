<?php

namespace App\Modules\Calificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Calificaciones\Requests\GuardarCalificacionRequest;
use App\Modules\Calificaciones\Services\RegistrarCalificacionService;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CalificacionJuradoController extends Controller
{
    public function evaluar(Presentacion $presentacion, Request $request): View|RedirectResponse
    {
        $usuario = $request->user();

        $juradoPresentacion = JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('id_usuario', $usuario->id)
            ->first();

        if (! $juradoPresentacion) {
            abort(403, 'Usted no está asignado como jurado para esta presentación.');
        }

        // Si la presentación aún no subió al escenario o no está participando
        if ($presentacion->estado === PresentacionEstadoEnum::POR_PARTICIPAR) {
            return redirect()->route('jurado.inicio')
                ->with('error', 'La presentación aún no ha sido llamada al escenario.');
        }

        $calificacion = $juradoPresentacion->calificacion()->with('detalles')->first();

        // Si ya emitió su calificación y está finalizada, redirigir a ver o corregir
        if ($calificacion && $calificacion->esEmitida()) {
            if ($presentacion->estado === PresentacionEstadoEnum::FINALIZADO) {
                return redirect()->route('jurado.calificaciones.corregir.form', $presentacion)
                    ->with('info', 'Ya emitió su calificación para esta presentación. Puede revisarla o corregirla.');
            }

            return redirect()->route('jurado.inicio')
                ->with('info', 'Ya emitió su calificación para esta presentación.');
        }

        $categoria = $presentacion->inscripcion?->categoria;

        if ($categoria && $categoria->estaFinalizada()) {
            return redirect()->route('jurado.inicio')
                ->with('error', sprintf('Las calificaciones para la categoría "%s" han sido cerradas.', $categoria->categoria));
        }

        // Marcar que el jurado empezó a evaluar
        if (! $calificacion) {
            $juradoPresentacion->estado = JuradoPresentacionEstadoEnum::CALIFICANDO;
            $juradoPresentacion->save();
        }

        $categoriaParametros = $categoria?->categoriaParametros()
            ->with('parametro')
            ->orderBy('orden')
            ->get() ?? collect();

        return view('jurado.calificar', compact('presentacion', 'juradoPresentacion', 'calificacion', 'categoriaParametros'));
    }

    public function guardar(Presentacion $presentacion, GuardarCalificacionRequest $request, RegistrarCalificacionService $service): RedirectResponse
    {
        $usuario = $request->user();

        $juradoPresentacion = JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('id_usuario', $usuario->id)
            ->first();

        if (! $juradoPresentacion) {
            abort(403, 'Usted no está asignado como jurado para esta presentación.');
        }

        try {
            $service->registrar(
                $juradoPresentacion,
                $request->input('valores', []),
                $usuario
            );

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', '¡Su calificación fue registrada exitosamente!');
            }

            return redirect()->route('jurado.inicio')
                ->with('success', '¡Su calificación fue registrada exitosamente!');
        } catch (DomainException|InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
