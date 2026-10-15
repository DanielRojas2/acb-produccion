<?php

namespace App\Modules\Categorias\Events;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoriaFinalizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Categoria $categoria) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('presentaciones'),
            new Channel('ranking'),
            new Channel('categoria.'.$this->categoria->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'categoria.finalizada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'categoria_id' => $this->categoria->id,
            'categoria_nombre' => $this->categoria->categoria,
            'finalizada' => true,
            'finalizada_at' => $this->categoria->finalizada_at?->toIso8601String() ?? now()->toIso8601String(),
            'jurado_secreto_revelado' => (bool) $this->categoria->jurado_secreto_revelado,
        ];
    }
}
