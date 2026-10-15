<?php

namespace App\Modules\Jurados\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Jurados\Services\GestionarJuradoSecretoService;
use App\Modules\Rankings\Services\CalcularRankingService;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\RolEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use UnexpectedValueException;

class AdminJuradoSecretoController extends Controller
{
    public function __construct(
        private readonly GestionarJuradoSecretoService $juradoSecretoService,
        private readonly CalcularRankingService $rankingService
    ) {}

    public function index(): View
    {
        Gate::authorize('asignarJurados');

        $resumenCategorias = $this->rankingService->resumenCategoriasEtapaFinal();
        $jurados = Usuario::where('rol', RolEnum::JURADO)->orderBy('nombre')->get();

        return view('admin.jurados.secreto', [
            'resumenCategorias' => $resumenCategorias,
            'jurados' => $jurados,
        ]);
    }

    public function designar(Request $request): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $validados = $request->validate([
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'usuario_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $categoria = Categoria::findOrFail((int) $validados['categoria_id']);

        try {
            $jurado = $this->juradoSecretoService->designarJuradoSecretoCategoria($categoria, (int) $validados['usuario_id']);
        } catch (UnexpectedValueException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', sprintf('Jurado secreto para "%s" asignado a %s %s.', $categoria->categoria, $jurado->nombre, $jurado->paterno));
    }

    public function prepararPodio(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $this->juradoSecretoService->prepararPodio($categoria);

        $msg = $categoria->tieneUnSoloGanador()
            ? sprintf('¡Podio preparado para "%s"! La pantalla pública está en Modo Misterio con carta tapada. Ya puede revelar al ganador oficial.', $categoria->categoria)
            : sprintf('¡Podio preparado para "%s"! La pantalla pública está en Modo Misterio con cartas tapadas. Ya puede revelar del 3° al 1° lugar.', $categoria->categoria);

        return back()->with('status', $msg);
    }

    public function revelar(Categoria $categoria): RedirectResponse
    {
        return $this->prepararPodio($categoria);
    }

    public function ocultar(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        try {
            $this->juradoSecretoService->ocultarJuradoSecreto($categoria);
            return back()->with('status', sprintf('Podio de "%s" desactivado. La pantalla pública ha vuelto a la vista de ranking estándar.', $categoria->categoria));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function finalizar(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $this->juradoSecretoService->finalizarCategoria($categoria);

        return back()->with('status', sprintf('Categoría "%s" FINALIZADA oficialmente. Jurado secreto revelado.', $categoria->categoria));
    }

    public function podioPaso(Request $request, Categoria $categoria): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $validados = $request->validate([
            'paso' => ['required', 'integer', 'in:0,1,2,3'],
        ]);

        $paso = (int) $validados['paso'];
        $this->juradoSecretoService->avanzarPasoPodio($categoria, $paso);

        if ($categoria->tieneUnSoloGanador()) {
            $mensajes = [
                0 => 'Modo misterio activado (carta tapada).',
                1 => sprintf('¡%s revelado y clasificación general desplegada!', $categoria->etiquetaGanador()),
            ];
        } else {
            $mensajes = [
                0 => 'Podio reiniciado en modo misterio (cartas tapadas).',
                3 => '¡3° Lugar revelado en el podio!',
                2 => '¡2° Lugar revelado en el podio!',
                1 => '¡1° Lugar revelado y clasificación general desplegada!',
            ];
        }

        return back()->with('status', $mensajes[$paso] ?? 'Paso de podio actualizado.');
    }

    public function reabrir(Categoria $categoria): RedirectResponse
    {
        Gate::authorize('asignarJurados');

        $this->juradoSecretoService->reabrirCategoria($categoria);

        return back()->with('status', sprintf('Categoría "%s" reabierta.', $categoria->categoria));
    }
}
