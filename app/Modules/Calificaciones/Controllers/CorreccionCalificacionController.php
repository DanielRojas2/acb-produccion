<?php

namespace App\Modules\Calificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Requests\CorregirCalificacionRequest;
use App\Modules\Calificaciones\Services\CorregirCalificacionService;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\PresentacionEstadoEnum;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CorreccionCalificacionController extends Controller
{
    public function formulario(Presentacion $presentacion, Request $request): View|RedirectResponse
    {
        $usuario = $request->user();

        $juradoPresentacion = JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('id_usuario', $usuario->id)
            ->first();

        if (! $juradoPresentacion && ! $usuario->esAdmin()) {
            abort(403, 'Usted no tiene permisos para corregir calificaciones en esta presentación.');
        }

        if ($presentacion->estado !== PresentacionEstadoEnum::FINALIZADO) {
            return redirect()->route('jurado.inicio')
                ->with('error', 'Solo es posible corregir calificaciones una vez que la presentación haya finalizado.');
        }

        $calificacion = $juradoPresentacion?->calificacion()->with('detalles')->first();

        if (! $calificacion) {
            return redirect()->route('jurado.inicio')
                ->with('error', 'No se encontró una calificación previa para corregir.');
        }

        $categoria = $presentacion->inscripcion?->categoria;

        if ($categoria && $categoria->estaFinalizada()) {
            return redirect()->route('jurado.inicio')
                ->with('error', sprintf('Las calificaciones para la categoría "%s" han sido cerradas. No se permiten más correcciones.', $categoria->categoria));
        }

        $categoriaParametros = $categoria?->categoriaParametros()
            ->with('parametro')
            ->orderBy('orden')
            ->get() ?? collect();

        $detallesActuales = $calificacion->detalles->keyBy('id_parametro');

        return view('jurado.corregir', compact('presentacion', 'juradoPresentacion', 'calificacion', 'categoriaParametros', 'detallesActuales'));
    }

    public function corregir(Calificacion $calificacion, CorregirCalificacionRequest $request, CorregirCalificacionService $service): RedirectResponse
    {
        $usuario = $request->user();

        try {
            $service->corregir(
                $calificacion,
                $request->input('valores', []),
                $usuario,
                $request->input('motivo')
            );

            $destino = $usuario->esAdmin()
                ? route('admin.calificaciones.presentacion', $calificacion->juradoPresentacion->id_presentacion)
                : route('jurado.inicio');

            return redirect($destino)->with('success', '¡La calificación fue corregida y registrada en el historial correctamente!');
        } catch (DomainException|InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
