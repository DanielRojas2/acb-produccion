<?php

namespace App\Modules\Jurados\Events;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Usuarios\Models\Usuario;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JuradoSecretoRevelado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Categoria $categoria,
        public readonly ?Usuario $juradoSecreto = null
    ) {}

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
        return 'jurado_secreto.revelado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'categoria_id' => $this->categoria->id,
            'categoria_nombre' => $this->categoria->categoria,
            'jurado_id' => $this->juradoSecreto?->id,
            'jurado_nombre' => $this->juradoSecreto ? "{$this->juradoSecreto->nombre} {$this->juradoSecreto->paterno}" : null,
            'jurado_secreto_revelado' => true,
            'revelado_at' => $this->categoria->revelado_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
