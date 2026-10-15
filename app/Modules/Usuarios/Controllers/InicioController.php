<?php

namespace App\Modules\Usuarios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Rankings\Services\CalcularRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __construct(
        private readonly CalcularRankingService $rankingService
    ) {}

    public function admin(): View
    {
        return view('admin.inicio');
    }

    public function jurado(Request $request): View
    {
        $usuario = $request->user();
        $esDelegadoMesa = $usuario && ($usuario->esAdmin() || $usuario->puedeControlarTiempo() || \App\Modules\Jurados\Models\JuradoPresentacion::where('id_usuario', $usuario->id)->where('es_controlador', true)->exists());

        $categorias = collect();
        $categoriaActiva = null;
        $presentacionesCategoria = collect();
        $resumenCategorias = collect();

        if (Schema::hasTable('categorias')) {
            $categorias = Categoria::orderBy('id')->get();
            $categoriaIdReq = $request->has('cat') ? $request->integer('cat') : null;
            $categoriaActiva = Categoria::categoriaActiva($categoriaIdReq);

            if ($categoriaActiva) {
                $presentacionesCategoria = \App\Modules\Presentaciones\Models\Presentacion::query()
                    ->select('presentaciones.*')
                    ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
                    ->join('fases', 'inscripciones.id_fase', '=', 'fases.id')
                    ->where('fases.fase', \App\Support\Enums\FaseEnum::FINAL->value)
                    ->where('inscripciones.id_categoria', $categoriaActiva->id)
                    ->with([
                        'inscripcion.categoria',
                        'inscripcion.departamento',
                        'inscripcion.participantes',
                        'jurados.usuario',
                        'jurados.calificacion.detalles.parametro',
                    ])
                    ->orderBy('inscripciones.fecha_presentacion')
                    ->orderBy('inscripciones.hora_presentacion')
                    ->orderBy('presentaciones.id')
                    ->get();
            }

            $resumenCategorias = $this->rankingService->resumenCategoriasEtapaFinal();
        }

        $presentacionEnVivo = $presentacionesCategoria->first(fn ($p) => in_array($p->estado, [
            \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO,
            \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO,
        ], true));

        $cronometroEnVivo = $presentacionEnVivo
            ? app(\App\Modules\Presentaciones\Services\GestionarCronometroService::class)->obtenerEstado($presentacionEnVivo)
            : null;

        return view('jurado.inicio', [
            'esDelegadoMesa' => $esDelegadoMesa,
            'categorias' => $categorias,
            'categoriaActiva' => $categoriaActiva,
            'presentacionesCategoria' => $presentacionesCategoria,
            'resumenCategorias' => $resumenCategorias,
            'presentacionEnVivo' => $presentacionEnVivo,
            'cronometroEnVivo' => $cronometroEnVivo,
        ]);
    }

    public function publico(Request $request): View
    {
        $categorias = Schema::hasTable('categorias') ? Categoria::orderBy('id')->get() : collect();
        
        $categoriaId = $request->has('categoria') ? $request->integer('categoria') : null;
        $categoriaSeleccionada = Categoria::categoriaActiva($categoriaId);

        $ranking = collect();
        if ($this->rankingService && $categoriaSeleccionada) {
            $ranking = $this->rankingService->rankingPorCategoria($categoriaSeleccionada, null, true);
        }

        $esPodio = (bool) ($categoriaSeleccionada?->estaRevelada() ?? false);
        $podioPaso = $categoriaSeleccionada?->podioPasoActual();
        $lugar1Revelado = $categoriaSeleccionada?->esPodioLugarRevelado(1) ?? false;
        $lugar2Revelado = $categoriaSeleccionada?->esPodioLugarRevelado(2) ?? false;
        $lugar3Revelado = $categoriaSeleccionada?->esPodioLugarRevelado(3) ?? false;
        $mostrarGeneral = $categoriaSeleccionada?->debeMostrarListadoGeneral() ?? false;

        $primerLugar = null;
        $segundoLugar = null;
        $tercerLugar = null;
        $restoParticipantes = collect();

        if ($esPodio && $ranking->isNotEmpty()) {
            $primerLugar = $ranking->firstWhere('posicion', 1);

            if ($categoriaSeleccionada?->tieneUnSoloGanador()) {
                $segundoLugar = null;
                $tercerLugar = null;
                $idsPodio = array_filter([$primerLugar['id'] ?? null]);
            } else {
                $segundoLugar = $ranking->firstWhere('posicion', 2);
                $tercerLugar = $ranking->firstWhere('posicion', 3);
                $idsPodio = array_filter([
                    $primerLugar['id'] ?? null,
                    $segundoLugar['id'] ?? null,
                    $tercerLugar['id'] ?? null,
                ]);
            }

            $restoParticipantes = $ranking->reject(fn ($item) => in_array($item['id'], $idsPodio, true))->values();
        }

        // Presentación en escenario
        $presentacionEnEscenario = \App\Modules\Presentaciones\Models\Presentacion::query()
            ->with(['inscripcion.categoria', 'inscripcion.departamento', 'inscripcion.participantes'])
            ->whereIn('estado', [
                \App\Support\Enums\PresentacionEstadoEnum::EN_ESCENARIO->value,
                \App\Support\Enums\PresentacionEstadoEnum::PARTICIPANDO->value,
            ])
            ->first();

        return view('publico.inicio', [
            'categorias' => $categorias,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'ranking' => $ranking,
            'esPodio' => $esPodio,
            'podioPaso' => $podioPaso,
            'lugar1Revelado' => $lugar1Revelado,
            'lugar2Revelado' => $lugar2Revelado,
            'lugar3Revelado' => $lugar3Revelado,
            'mostrarGeneral' => $mostrarGeneral,
            'primerLugar' => $primerLugar,
            'segundoLugar' => $segundoLugar,
            'tercerLugar' => $tercerLugar,
            'restoParticipantes' => $restoParticipantes,
            'presentacionEnEscenario' => $presentacionEnEscenario,
        ]);
    }
}