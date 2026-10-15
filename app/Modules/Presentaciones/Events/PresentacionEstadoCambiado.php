<?php

namespace App\Modules\Presentaciones\Events;

use App\Modules\Presentaciones\Models\Presentacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresentacionEstadoCambiado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Presentacion $presentacion) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('ranking'),
            new Channel('presentaciones'),
            new PrivateChannel('presentaciones'),
            new PrivateChannel('presentaciones.'.$this->presentacion->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'presentacion.estado.cambiado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $inscripcion = $this->presentacion->inscripcion;
        $cronometro = app(\App\Modules\Presentaciones\Services\GestionarCronometroService::class)->obtenerEstado($this->presentacion);

        return [
            'presentacion_id' => $this->presentacion->id,
            'nombre_presentacion' => $this->presentacion->nombreMostrado(),
            'estado' => $this->presentacion->estado?->value,
            'estado_label' => $this->presentacion->estado?->label(),
            'categoria' => $inscripcion?->categoria?->categoria,
            'fase' => $inscripcion?->fase?->fase?->value,
            'fase_label' => $inscripcion?->fase?->fase?->label(),
            'departamento' => $inscripcion?->departamento?->nombre,
            'hora_inicio' => $this->presentacion->hora_inicio?->format('H:i'),
            'hora_fin' => $this->presentacion->hora_fin?->format('H:i'),
            'tiempo_maximo' => $this->presentacion->tiempo_maximo,
            'cronometro_iniciado_at' => $this->presentacion->cronometro_iniciado_at?->toIso8601String(),
            'cronometro_finalizado_at' => $this->presentacion->cronometro_finalizado_at?->toIso8601String(),
            'cronometro_iniciado_at_ms' => $cronometro['cronometro_iniciado_at_ms'],
            'cronometro_finalizado_at_ms' => $cronometro['cronometro_finalizado_at_ms'],
            'server_now_ms' => $cronometro['server_now_ms'],
            'cronometro' => $cronometro,
            'corriendo' => $cronometro['corriendo'],
            'segundos_transcurridos' => $cronometro['segundos_transcurridos'],
            'segundos_restantes' => $cronometro['segundos_restantes'],
        ];
    }
}