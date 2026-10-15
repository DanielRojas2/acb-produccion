<?php

namespace App\Modules\Presentaciones\Services;

use App\Modules\Presentaciones\Events\PresentacionEstadoCambiado;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\PresentacionEstadoEnum;

/**
 * Controla las transiciones de estado de una presentación.
 * El cronómetro se integra automáticamente según la fase en que esté,
 * pero también puede operarse de forma manual con GestionarCronometroService.
 */
class GestionarPresentacionService
{
    public function __construct(private readonly GestionarCronometroService $cronometro) {}

    /**
     * Llama a la presentación al escenario y deja el cronómetro en cero.
     */
    public function alEscenario(Presentacion $presentacion): Presentacion
    {
        $this->validarTransicion($presentacion, PresentacionEstadoEnum::EN_ESCENARIO);

        $presentacion->forceFill([
            'estado' => PresentacionEstadoEnum::EN_ESCENARIO,
            'hora_inicio' => now()->format('H:i'),
            'cronometro_iniciado_at' => null,
            'cronometro_finalizado_at' => null,
        ])->save();

        $this->emitirEvento($presentacion);

        return $presentacion;
    }

    /**
     * Inicia la presentación; el cronómetro arranca automáticamente
     * si aún no había sido iniciado por el controlador.
     */
    public function iniciarPresentacion(Presentacion $presentacion): Presentacion
    {
        $this->validarTransicion($presentacion, PresentacionEstadoEnum::PARTICIPANDO);

        $presentacion->forceFill([
            'estado' => PresentacionEstadoEnum::PARTICIPANDO,
            'cronometro_iniciado_at' => $presentacion->cronometro_iniciado_at ?? now(),
            'cronometro_finalizado_at' => null,
        ])->save();

        $this->emitirEvento($presentacion);
        $this->cronometro->emitirEvento($presentacion);

        return $presentacion;
    }

    /**
     * Finaliza la presentación; el cronómetro se detiene si estaba en curso.
     */
    public function finalizarPresentacion(Presentacion $presentacion): Presentacion
    {
        $this->validarTransicion($presentacion, PresentacionEstadoEnum::FINALIZADO);

        $presentacion->forceFill([
            'estado' => PresentacionEstadoEnum::FINALIZADO,
            'hora_fin' => now()->format('H:i'),
            'cronometro_finalizado_at' => $presentacion->cronometroCorriendo()
                ? now()
                : $presentacion->cronometro_finalizado_at,
        ])->save();

        $this->emitirEvento($presentacion);
        $this->cronometro->emitirEvento($presentacion);

        return $presentacion;
    }

    /**
     * Aplica una transición genérica cuando la regla lo permite.
     */
    public function transicionarAEstado(Presentacion $presentacion, PresentacionEstadoEnum $estado): Presentacion
    {
        $this->validarTransicion($presentacion, $estado);

        $presentacion->forceFill(['estado' => $estado])->save();

        $this->emitirEvento($presentacion);

        return $presentacion;
    }

    private function emitirEvento(Presentacion $presentacion): void
    {
        try {
            event(new PresentacionEstadoCambiado($presentacion));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo transmitir evento WebSocket de presentación: '.$e->getMessage());
        }
    }

    private function validarTransicion(Presentacion $presentacion, PresentacionEstadoEnum $destino): void
    {
        $actual = $presentacion->estado;

        if (! $actual?->puedeTransicionarA($destino)) {
            throw new \UnexpectedValueException(sprintf(
                'No se puede pasar la presentación de "%s" a "%s".',
                $actual?->label() ?? '—',
                $destino->label()
            ));
        }
    }
}