<?php

namespace App\Modules\Jurados\Events;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PodioPasoRevelado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<string, mixed>|null $datosGanador
     * @param array<int, mixed>|null $ranking
     */
    public function __construct(
        public readonly Categoria $categoria,
        public readonly int $paso,
        public readonly ?int $posicionRevelada = null,
        public readonly ?array $datosGanador = null,
        public readonly ?array $ranking = null
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
        return 'podio.paso_revelado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $ganadorLimpio = null;
        if ($this->datosGanador) {
            $ganadorLimpio = [
                'id' => $this->datosGanador['id'] ?? null,
                'nombre' => $this->datosGanador['nombre'] ?? null,
                'departamento' => $this->datosGanador['departamento'] ?? null,
                'nota_final' => $this->datosGanador['nota_final'] ?? null,
                'foto_url' => $this->datosGanador['foto_url'] ?? null,
            ];
        }

        return [
            'categoria_id' => $this->categoria->id,
            'categoria_nombre' => $this->categoria->categoria,
            'paso' => $this->paso,
            'posicion_revelada' => $this->posicionRevelada,
            'mostrar_general' => $this->paso === 1,
            'ganador' => $ganadorLimpio,
        ];
    }
}
