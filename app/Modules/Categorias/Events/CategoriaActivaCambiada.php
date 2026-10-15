<?php

namespace App\Modules\Categorias\Events;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoriaActivaCambiada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Categoria $categoria
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('ranking'),
            new Channel('presentaciones'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'categoria.activa.cambiada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'categoria_id' => $this->categoria->id,
            'categoria_nombre' => $this->categoria->categoria,
            'finalizada' => (bool) $this->categoria->finalizada,
            'revelada' => (bool) $this->categoria->estaRevelada(),
        ];
    }
}
