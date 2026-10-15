<?php

namespace App\Modules\Jurados\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Events\CategoriaActivaCambiada;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Jurados\Services\GestionarJuradoSecretoService;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Presentaciones\Services\GestionarPresentacionService;
use App\Modules\Rankings\Events\RankingActualizado;
use App\Modules\Rankings\Services\CalcularRankingService;
use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use UnexpectedValueException;

class MesaJuradoController extends Controller
{
    public function __construct(
        private readonly GestionarJuradoSecretoService $juradoSecretoService,
        private readonly CalcularRankingService $rankingService,
        private readonly GestionarPresentacionService $presentacionesService,
    ) {}

    public function seleccionarCategoria(Request $request, Categoria $categoria): RedirectResponse
    {
        Gate::authorize('controlarMesa');

        $categoria->marcarActiva();

        event(new CategoriaActivaCambiada($categoria));

        $ranking = $this->rankingService->rankingPorCategoria($categoria, null, true);
        event(new RankingActualizado($categoria, $ranking));

        return back();
    }

    public function cerrarCalificaciones(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('controlarMesa');

        $this->juradoSecretoService->finalizarCategoria($categoria);

        return back()->with('status', sprintf('Calificaciones de la categoría "%s" cerradas exitosamente. Posiciones actualizadas.', $categoria->categoria));
    }

    public function revelarGanador(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('controlarMesa');

        $this->juradoSecretoService->revelarJuradoSecreto($categoria, 0);

        $msg = $categoria->tieneUnSoloGanador()
            ? sprintf('¡Ganador y jurado secreto de "%s" revelados en Modo Misterio! Proceda a revelar al ganador oficial.', $categoria->categoria)
            : sprintf('¡Ganador y jurado secreto de "%s" revelados en Modo Misterio! Proceda a revelar el 3°, 2° y 1° lugar.', $categoria->categoria);

        return back()->with('status', $msg);
    }

    public function podioPaso(Request $request, Categoria $categoria): RedirectResponse
    {
        Gate::authorize('controlarMesa');

        $validados = $request->validate([
            'paso' => ['required', 'integer', 'in:0,1,2,3'],
        ]);

        $paso = (int) $validados['paso'];
        $this->juradoSecretoService->avanzarPasoPodio($categoria, $paso);

        if ($categoria->tieneUnSoloGanador()) {
            $mensajes = [
                0 => 'Modo misterio activado (carta tapada).',
                1 => sprintf('¡%s revelado en vivo!', $categoria->etiquetaGanador()),
            ];
        } else {
            $mensajes = [
                0 => 'Podio reiniciado en modo misterio.',
                3 => '¡3° Lugar revelado en vivo!',
                2 => '¡2° Lugar revelado en vivo!',
                1 => '¡1° Lugar revelado y podio completado!',
            ];
        }

        return back()->with('status', $mensajes[$paso] ?? 'Paso de podio actualizado.');
    }

    public function transicion(Request $request, Presentacion $presentacion): RedirectResponse
    {
        Gate::authorize('controlarMesa');

        $estado = PresentacionEstadoEnum::tryFrom($request->string('estado')->toString());

        if ($estado === null) {
            return back()->with('error', 'Estado de destino no válido.');
        }

        try {
            $presentacion = match ($estado) {
                PresentacionEstadoEnum::EN_ESCENARIO => $this->presentacionesService->alEscenario($presentacion),
                PresentacionEstadoEnum::PARTICIPANDO => $this->presentacionesService->iniciarPresentacion($presentacion),
                PresentacionEstadoEnum::FINALIZADO => $this->presentacionesService->finalizarPresentacion($presentacion),
                default => throw new UnexpectedValueException('Estado no controlable.'),
            };

            // Asegurar que la categoría de esta presentación sea la activa
            if ($presentacion->inscripcion?->categoria) {
                $presentacion->inscripcion->categoria->marcarActiva();
                event(new CategoriaActivaCambiada($presentacion->inscripcion->categoria));
            }
        } catch (UnexpectedValueException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf('Presentación de "%s" marcada como "%s".', $presentacion->nombreMostrado(), $presentacion->estado?->label()));
    }
}
