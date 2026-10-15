<?php

namespace App\Modules\Rankings\Events;

use App\Modules\Categorias\Models\Categoria;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RankingActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $ranking
     */
    public function __construct(
        public readonly Categoria $categoria,
        public readonly Collection|array $ranking
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('ranking'),
            new Channel('categoria.'.$this->categoria->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ranking.actualizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $items = $this->ranking instanceof Collection ? $this->ranking : collect($this->ranking);
        $rankingSlim = $items->map(function ($item) {
            $arr = is_array($item) ? $item : (array) $item;
            unset($arr['jurados']);
            return $arr;
        })->values()->all();

        return [
            'categoria_id' => $this->categoria->id,
            'categoria_nombre' => $this->categoria->categoria,
            'jurado_secreto_revelado' => (bool) $this->categoria->jurado_secreto_revelado,
            'finalizada' => (bool) $this->categoria->finalizada,
            'ranking' => $rankingSlim,
        ];
    }
}
