<?php

namespace App\Modules\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Reportes\Services\GenerarReporteService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    public function __construct(
        protected GenerarReporteService $reporteService
    ) {}

    /**
     * Muestra el centro principal de reportes y actas oficiales.
     */
    public function index(Request $request): View
    {
        $fases = Fase::orderBy('id')->get();
        $categorias = Categoria::orderBy('id')->get();
        $departamentos = Departamento::orderBy('nombre')->get();

        $faseSeleccionada = $request->filled('fase_id')
            ? Fase::find($request->integer('fase_id'))
            : $fases->first();

        $categoriaSeleccionada = $request->filled('categoria_id')
            ? Categoria::find($request->integer('categoria_id'))
            : $categorias->first();

        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        $reporte = null;
        if ($faseSeleccionada && $categoriaSeleccionada) {
            $reporte = $this->reporteService->obtenerResultados($faseSeleccionada, $categoriaSeleccionada, $departamentoId);
        }

        return view('admin.reportes.index', compact(
            'fases',
            'categorias',
            'departamentos',
            'faseSeleccionada',
            'categoriaSeleccionada',
            'departamentoId',
            'reporte'
        ));
    }

    /**
     * Vista de resultados oficiales y ranking por categoría.
     */
    public function resultados(Request $request): View
    {
        $fase = Fase::findOrFail($request->integer('fase_id', 1));
        $categoria = Categoria::findOrFail($request->integer('categoria_id', 1));
        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        $reporte = $this->reporteService->obtenerResultados($fase, $categoria, $departamentoId);
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('admin.reportes.resultados', compact('reporte', 'departamentos'));
    }

    /**
     * Acta oficial de calificaciones y escrutinio (optimizada para impresión y Notaría de Fe Pública).
     */
    public function acta(Request $request): View
    {
        $fase = Fase::findOrFail($request->integer('fase_id', 1));
        $categoria = Categoria::findOrFail($request->integer('categoria_id', 1));
        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        $reporte = $this->reporteService->obtenerResultados($fase, $categoria, $departamentoId);

        return view('admin.reportes.acta', compact('reporte'));
    }

    /**
     * Sábana completa de calificaciones por jurado y parámetro.
     */
    public function sabana(Request $request): View
    {
        $fase = Fase::findOrFail($request->integer('fase_id', 1));
        $categoria = Categoria::findOrFail($request->integer('categoria_id', 1));
        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        $sabana = $this->reporteService->obtenerSabanaDetallada($fase, $categoria, $departamentoId);

        return view('admin.reportes.sabana', compact('sabana'));
    }

    /**
     * Descarga de resultados oficiales en CSV.
     */
    public function exportarResultadosCsv(Request $request): StreamedResponse
    {
        $fase = Fase::findOrFail($request->integer('fase_id', 1));
        $categoria = Categoria::findOrFail($request->integer('categoria_id', 1));
        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        return $this->reporteService->exportarCsvResultados($fase, $categoria, $departamentoId);
    }

    /**
     * Descarga de sábana detallada en CSV.
     */
    public function exportarSabanaCsv(Request $request): StreamedResponse
    {
        $fase = Fase::findOrFail($request->integer('fase_id', 1));
        $categoria = Categoria::findOrFail($request->integer('categoria_id', 1));
        $departamentoId = $request->filled('departamento_id') ? $request->integer('departamento_id') : null;

        return $this->reporteService->exportarCsvSabana($fase, $categoria, $departamentoId);
    }
}
