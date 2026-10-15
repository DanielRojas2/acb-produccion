<?php

namespace App\Modules\Auditoria\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Calificaciones\Models\CalificacionHistorial;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Usuarios\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /**
     * Muestra el listado de historial de correcciones y auditoría con filtros.
     */
    public function index(Request $request): View
    {
        $query = CalificacionHistorial::with([
            'calificacion.juradoPresentacion.usuario',
            'calificacion.juradoPresentacion.presentacion.inscripcion.categoria',
            'calificacion.juradoPresentacion.presentacion.inscripcion.departamento',
            'calificacion.juradoPresentacion.presentacion.inscripcion.participantes',
            'usuario',
        ])->latest('fecha');

        if ($request->filled('categoria_id')) {
            $query->whereHas('calificacion.juradoPresentacion.presentacion.inscripcion', function ($q) use ($request) {
                $q->where('id_categoria', $request->integer('categoria_id'));
            });
        }

        if ($request->filled('usuario_id')) {
            $query->where('id_usuario', $request->integer('usuario_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($uq) use ($buscar) {
                        $uq->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('paterno', 'like', "%{$buscar}%")
                            ->orWhere('username', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('calificacion.juradoPresentacion.presentacion', function ($pq) use ($buscar) {
                        $pq->where('nombre_presentacion', 'like', "%{$buscar}%")
                            ->orWhereHas('inscripcion.participantes', function ($partQ) use ($buscar) {
                                $partQ->where('nombre', 'like', "%{$buscar}%")
                                    ->orWhere('paterno', 'like', "%{$buscar}%")
                                    ->orWhere('materno', 'like', "%{$buscar}%")
                                    ->orWhere('pseudonimo', 'like', "%{$buscar}%");
                            });
                    });
            });
        }

        $historiales = $query->paginate(15)->withQueryString();
        $categorias = Categoria::orderBy('id')->get();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('admin.auditoria.index', compact('historiales', 'categorias', 'usuarios'));
    }

    /**
     * Muestra el detalle pormenorizado de una corrección con comparación de snapshots.
     */
    public function show(CalificacionHistorial $historial): View
    {
        $historial->load([
            'calificacion.juradoPresentacion.usuario',
            'calificacion.juradoPresentacion.presentacion.inscripcion.categoria.categoriaParametros.parametro',
            'calificacion.juradoPresentacion.presentacion.inscripcion.departamento',
            'calificacion.juradoPresentacion.presentacion.inscripcion.participantes',
            'usuario',
        ]);

        $presentacion = $historial->calificacion?->juradoPresentacion?->presentacion;
        $categoria = $presentacion?->inscripcion?->categoria;
        $parametros = $categoria ? $categoria->categoriaParametros->keyBy('id_parametro') : collect();

        // Mapeo detallado de diferencias por parámetro
        $detallesAnteriores = collect($historial->datos_anteriores['detalles'] ?? [])->keyBy('id_parametro');
        $detallesNuevos = collect($historial->datos_nuevos['detalles'] ?? [])->keyBy('id_parametro');

        $comparativaParametros = [];
        foreach ($parametros as $idParam => $cp) {
            $valorAnt = (float) ($detallesAnteriores->get($idParam)['valor'] ?? 0);
            $valorNue = (float) ($detallesNuevos->get($idParam)['valor'] ?? 0);
            $diferencia = round($valorNue - $valorAnt, 2);

            $comparativaParametros[] = [
                'id_parametro' => $idParam,
                'nombre' => $cp->parametro?->parametro ?? "Parámetro #{$idParam}",
                'valor_minimo' => (float) $cp->valor_minimo,
                'valor_maximo' => (float) $cp->valor_maximo,
                'valor_anterior' => $valorAnt,
                'valor_nuevo' => $valorNue,
                'diferencia' => $diferencia,
            ];
        }

        $notaTotalAnterior = (float) ($historial->datos_anteriores['nota_total'] ?? 0);
        $notaTotalNueva = (float) ($historial->datos_nuevos['nota_total'] ?? 0);
        $diferenciaTotal = round($notaTotalNueva - $notaTotalAnterior, 2);

        return view('admin.auditoria.show', compact(
            'historial',
            'presentacion',
            'categoria',
            'comparativaParametros',
            'notaTotalAnterior',
            'notaTotalNueva',
            'diferenciaTotal'
        ));
    }
}
