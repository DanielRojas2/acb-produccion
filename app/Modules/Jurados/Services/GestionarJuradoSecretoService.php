<?php

namespace App\Modules\Jurados\Services;

use App\Modules\Categorias\Events\CategoriaActivaCambiada;
use App\Modules\Categorias\Events\CategoriaFinalizada;
use App\Modules\Categorias\Models\Categoria;
use App\Modules\Jurados\Events\JuradoSecretoRevelado;
use App\Modules\Jurados\Events\PodioPasoRevelado;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Rankings\Events\RankingActualizado;
use App\Modules\Rankings\Services\CalcularRankingService;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

class GestionarJuradoSecretoService
{
    public function __construct(
        private readonly AsignarJuradosService $asignarJuradosService,
        private readonly CalcularRankingService $calcularRankingService
    ) {}

    /**
     * Designa un jurado secreto para todas las presentaciones de una categoría en la Etapa Final.
     */
    public function designarJuradoSecretoCategoria(Categoria $categoria, int $usuarioId): Usuario
    {
        $jurado = Usuario::where('rol', RolEnum::JURADO)->find($usuarioId);

        if (! $jurado) {
            throw new UnexpectedValueException('El usuario especificado no es un jurado válido.');
        }

        DB::transaction(function () use ($categoria, $jurado) {
            $categoria->update([
                'jurado_secreto_usuario_id' => $jurado->id,
            ]);

            $presentaciones = Presentacion::query()
                ->whereHas('inscripcion', function ($q) use ($categoria) {
                    $q->where('id_categoria', $categoria->id)
                        ->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value));
                })
                ->get();

            foreach ($presentaciones as $pres) {
                $juradosActuales = JuradoPresentacion::where('id_presentacion', $pres->id)->get();

                if ($juradosActuales->count() === 5) {
                    JuradoPresentacion::where('id_presentacion', $pres->id)
                        ->update(['es_secreto' => false]);

                    $jp = JuradoPresentacion::where('id_presentacion', $pres->id)
                        ->where('id_usuario', $jurado->id)
                        ->first();

                    if ($jp) {
                        $jp->update(['es_secreto' => true]);
                    } else {
                        $ultimoJurado = $juradosActuales->last();
                        if ($ultimoJurado) {
                            $ultimoJurado->update([
                                'id_usuario' => $jurado->id,
                                'es_secreto' => true,
                                'es_controlador' => false,
                                'estado' => \App\Support\Enums\JuradoPresentacionEstadoEnum::ASIGNADO,
                            ]);
                        }
                    }
                }
            }
        });

        return $jurado;
    }

    /**
     * Prepara el terreno del podio para una categoría:
     * - Calcula las notas finales incluyendo jurado secreto
     * - Marca la categoría como activa y revela en Modo Misterio (paso 0)
     * - Emite eventos para sincronizar pantallas en vivo
     */
    public function prepararPodio(Categoria $categoria): void
    {
        $this->revelarJuradoSecreto($categoria, 0);
    }

    /**
     * Revela el jurado secreto e inicia el podio de una categoría en la etapa final.
     * Por defecto inicia en paso 0 (modo misterio, cartas tapadas).
     */
    public function revelarJuradoSecreto(Categoria $categoria, ?int $paso = 0): void
    {
        $pasoEfectivo = $paso ?? 0;
        $categoria->marcarRevelada($pasoEfectivo);
        $categoria->marcarActiva();

        event(new CategoriaActivaCambiada($categoria));

        $juradoSecreto = $this->asignarJuradosService->juradoSecretoDeCategoria($categoria);

        // Despachar eventos en tiempo real
        event(new JuradoSecretoRevelado($categoria, $juradoSecreto));

        $ranking = $this->calcularRankingService->rankingPorCategoria($categoria, null, true);
        event(new PodioPasoRevelado($categoria, $pasoEfectivo));
        event(new RankingActualizado($categoria, $ranking));
    }

    /**
     * Avanza o cambia el paso de revelación del podio (0=todo tapado, 3=revelar 3°, 2=revelar 2°, 1=revelar 1° y general).
     */
    public function avanzarPasoPodio(Categoria $categoria, int $paso): void
    {
        $categoria->revelarPasoPodio($paso);

        $ranking = $this->calcularRankingService->rankingPorCategoria($categoria, null, true);

        $posicion = in_array($paso, [1, 2, 3], true) ? $paso : null;
        $datosGanador = $posicion ? $ranking->firstWhere('posicion', $posicion) : null;

        $ganadorData = null;
        if ($datosGanador) {
            $ganadorData = [
                'id' => $datosGanador['id'] ?? null,
                'nombre' => $datosGanador['nombre'] ?? null,
                'departamento' => $datosGanador['departamento'] ?? null,
                'nota_final' => $datosGanador['nota_final'] ?? null,
                'foto_url' => $datosGanador['foto_url'] ?? null,
            ];
        }

        event(new PodioPasoRevelado(
            $categoria,
            $paso,
            $posicion,
            $ganadorData
        ));

        event(new RankingActualizado($categoria, $ranking));
    }

    /**
     * Oculta nuevamente el jurado secreto de una categoría (si no está finalizada).
     */
    public function ocultarJuradoSecreto(Categoria $categoria): void
    {
        if ($categoria->estaFinalizada()) {
            throw new UnexpectedValueException('No se puede ocultar el jurado secreto de una categoría ya finalizada.');
        }

        $categoria->marcarOculta();

        $ranking = $this->calcularRankingService->rankingPorCategoria($categoria, null, true);
        event(new RankingActualizado($categoria, $ranking));
    }

    /**
     * Finaliza/cierra oficialmente las calificaciones de la categoría.
     */
    public function finalizarCategoria(Categoria $categoria): void
    {
        $categoria->marcarFinalizada();

        event(new CategoriaFinalizada($categoria));

        $ranking = $this->calcularRankingService->rankingPorCategoria($categoria, null, true);
        event(new RankingActualizado($categoria, $ranking));
    }

    /**
     * Reabre una categoría finalizada.
     */
    public function reabrirCategoria(Categoria $categoria): void
    {
        $categoria->marcarReabierta();

        $ranking = $this->calcularRankingService->rankingPorCategoria($categoria, null, true);
        event(new RankingActualizado($categoria, $ranking));
    }

    public function obtenerJuradoSecreto(Categoria $categoria): ?Usuario
    {
        return $this->asignarJuradosService->juradoSecretoDeCategoria($categoria);
    }

    public function estaRevelado(Categoria $categoria): bool
    {
        return $categoria->estaRevelada();
    }

    public function estaFinalizada(Categoria $categoria): bool
    {
        return $categoria->estaFinalizada();
    }
}
