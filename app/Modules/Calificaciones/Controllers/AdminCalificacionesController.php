<?php

namespace App\Modules\Calificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Presentaciones\Models\Presentacion;
use Illuminate\View\View;

class AdminCalificacionesController extends Controller
{
    public function presentacion(Presentacion $presentacion): View
    {
        $presentacion->load([
            'inscripcion.categoria.categoriaParametros.parametro',
            'inscripcion.departamento',
            'inscripcion.fase',
            'jurados.usuario',
            'jurados.calificacion.detalles.parametro',
            'jurados.calificacion.historiales.usuario',
        ]);

        return view('admin.calificaciones.detalle', compact('presentacion'));
    }

    public function historial(Calificacion $calificacion): View
    {
        $calificacion->load([
            'juradoPresentacion.usuario',
            'juradoPresentacion.presentacion.inscripcion.categoria.categoriaParametros.parametro',
            'historiales.usuario',
            'detalles.parametro',
        ]);

        return view('admin.calificaciones.historial', compact('calificacion'));
    }
}
