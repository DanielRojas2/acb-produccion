<?php

namespace App\Modules\Presentaciones\Events;

use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Presentaciones\Services\GestionarCronometroService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CronometroActualizado implements ShouldBroadcastNow
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
        return 'cronometro.actualizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $estado = app(GestionarCronometroService::class)->obtenerEstado($this->presentacion);
        $inscripcion = $this->presentacion->inscripcion;

        return [
            ...$estado,
            'nombre_presentacion' => $this->presentacion->nombreMostrado(),
            'estado' => $this->presentacion->estado?->value,
            'estado_label' => $this->presentacion->estado?->label(),
            'categoria' => $inscripcion?->categoria?->categoria,
            'fase' => $inscripcion?->fase?->fase?->value,
            'fase_label' => $inscripcion?->fase?->fase?->label(),
            'departamento' => $inscripcion?->departamento?->nombre,
        ];
    }
}