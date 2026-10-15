<?php

namespace App\Modules\Calificaciones\Events;

use App\Modules\Calificaciones\Models\Calificacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CalificacionRegistrada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Calificacion $calificacion) {}

    public function broadcastOn(): array
    {
        $presentacionId = $this->calificacion->juradoPresentacion?->id_presentacion;

        return [
            new Channel('ranking'),
            new Channel('presentaciones'),
            new PrivateChannel('presentaciones'),
            new PrivateChannel('presentaciones.'.$presentacionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'calificacion.registrada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $juradoPresentacion = $this->calificacion->juradoPresentacion;
        $presentacion = $juradoPresentacion?->presentacion;

        return [
            'calificacion_id' => $this->calificacion->id,
            'presentacion_id' => $presentacion?->id,
            'jurado_id' => $juradoPresentacion?->id_usuario,
            'jurado_nombre' => $juradoPresentacion?->usuario?->nombre,
            'es_secreto' => $juradoPresentacion?->es_secreto,
            'nota_total' => $this->calificacion->nota_total,
            'nota_final' => $presentacion?->nota_final,
            'estado' => $this->calificacion->estado?->value,
            'fecha_finalizacion' => $this->calificacion->fecha_finalizacion?->toIso8601String(),
        ];
    }
}
