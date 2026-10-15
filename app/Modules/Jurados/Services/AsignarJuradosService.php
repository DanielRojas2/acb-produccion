<?php

namespace App\Modules\Jurados\Services;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use App\Support\Enums\RolEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Gestiona la asignación de jurados a las presentaciones.
 * - Preselección: exactamente 3 jurados.
 * - Final: exactamente 5 jurados.
 */
class AsignarJuradosService
{
    public function cantidadRequerida(Presentacion $presentacion): int
    {
        return $presentacion->esPreseleccion() ? 3 : 5;
    }

    public function cantidadAsignada(Presentacion $presentacion): int
    {
        return $this->asignaciones($presentacion)->count();
    }

    public function estaCompleta(Presentacion $presentacion): bool
    {
        return $this->cantidadAsignada($presentacion) === $this->cantidadRequerida($presentacion);
    }

    /**
     * @return Collection<int, JuradoPresentacion>
     */
    public function asignaciones(Presentacion $presentacion): Collection
    {
        return JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->with('usuario')
            ->get();
    }

    /**
     * Obtiene el jurado secreto asignado a una categoría en la etapa final (si existe).
     */
    public function juradoSecretoDeCategoria(?\App\Modules\Categorias\Models\Categoria $categoria): ?Usuario
    {
        if (! $categoria) {
            return null;
        }

        if ($categoria->jurado_secreto_usuario_id) {
            return $categoria->juradoSecreto ?? Usuario::find($categoria->jurado_secreto_usuario_id);
        }

        $asignacionSecreta = JuradoPresentacion::where('es_secreto', true)
            ->whereHas('presentacion.inscripcion', function ($query) use ($categoria) {
                $query->where('id_categoria', $categoria->id)
                    ->whereHas('fase', fn ($f) => $f->where('fase', \App\Support\Enums\FaseEnum::FINAL->value));
            })
            ->with('usuario')
            ->first();

        return $asignacionSecreta?->usuario;
    }

    /**
     * Asigna automáticamente los puestos faltantes, rotando a los jurados
     * con menos asignaciones en el festival para distribuir la carga.
     * En la Etapa Final, garantiza la inclusión y designación de 1 jurado secreto.
     *
     * @return list<JuradoPresentacion>
     */
    public function autoAsignar(Presentacion $presentacion): array
    {
        $requerido = $this->cantidadRequerida($presentacion);
        $faltantes = $requerido - $this->cantidadAsignada($presentacion);

        if ($faltantes <= 0) {
            return [];
        }

        $disponibles = $this->juradosDisponibles($presentacion);

        if ($disponibles->count() < $faltantes) {
            throw new \UnexpectedValueException(sprintf(
                'No hay suficientes jurados disponibles: se requieren %d y solo hay %d.',
                $faltantes,
                $disponibles->count()
            ));
        }

        $esFinal = ! $presentacion->esPreseleccion();
        $categoria = $presentacion->inscripcion?->categoria;
        $secretoCategoria = $esFinal ? $this->juradoSecretoDeCategoria($categoria) : null;
        $yaTieneSecreto = $presentacion->jurados()->where('es_secreto', true)->exists();

        // Si es final y la categoría ya tiene un jurado secreto pero esta presentación no lo tiene asignado
        $elegidos = collect();
        if ($esFinal && $secretoCategoria && ! $yaTieneSecreto) {
            $juradoSecreto = $disponibles->firstWhere('id', $secretoCategoria->id);
            if ($juradoSecreto) {
                $elegidos->push($juradoSecreto);
                $disponibles = $disponibles->reject(fn ($j) => $j->id === $secretoCategoria->id)->values();
                $faltantes--;
            }
        }

        if ($faltantes > 0) {
            $elegidos = $elegidos->concat($disponibles->take($faltantes));
        }

        return DB::transaction(function () use ($presentacion, $elegidos, $esFinal, $secretoCategoria, $yaTieneSecreto) {
            $creadas = [];
            $secretoDesignado = $yaTieneSecreto;

            foreach ($elegidos as $index => $jurado) {
                $esSecreto = false;

                if ($esFinal && ! $secretoDesignado) {
                    if ($secretoCategoria && $jurado->id === $secretoCategoria->id) {
                        $esSecreto = true;
                        $secretoDesignado = true;
                    } elseif (! $secretoCategoria && ($index === count($elegidos) - 1 || count($elegidos) === 5)) {
                        $esSecreto = true;
                        $secretoDesignado = true;
                    }
                }

                $creadas[] = $this->crearAsignacion($presentacion, $jurado, $esSecreto);
            }

            return $creadas;
        });
    }

    /**
     * Reemplaza por completo la lista de jurados de una presentación.
     *
     * @param  list<int>  $usuarioIds
     * @param  int|null  $secretoUsuarioId
     * @return list<JuradoPresentacion>
     */
    public function asignar(Presentacion $presentacion, array $usuarioIds, ?int $secretoUsuarioId = null): array
    {
        $requerido = $this->cantidadRequerida($presentacion);

        if (count($usuarioIds) !== $requerido) {
            throw new \UnexpectedValueException(sprintf(
                'La presentación requiere exactamente %d jurados y se indicaron %d.',
                $requerido,
                count($usuarioIds)
            ));
        }

        // Validación de jurado secreto
        if ($presentacion->esPreseleccion() && $secretoUsuarioId !== null) {
            throw new \UnexpectedValueException('La etapa de preselección no permite asignar un jurado secreto.');
        }

        if ($secretoUsuarioId !== null && ! in_array((int) $secretoUsuarioId, array_map('intval', $usuarioIds), true)) {
            throw new \UnexpectedValueException('El jurado secreto designado debe pertenecer al grupo de jurados asignados.');
        }

        $jurados = $this->juradosValidos($usuarioIds);

        if ($jurados->count() !== $requerido) {
            throw new \UnexpectedValueException('Algunos de los usuarios indicados no son jurados válidos.');
        }

        // Si es etapa final y no se especificó $secretoUsuarioId, verificar si la categoría ya tiene un jurado secreto
        if (! $presentacion->esPreseleccion() && $secretoUsuarioId === null) {
            $secretoCat = $this->juradoSecretoDeCategoria($presentacion->inscripcion?->categoria);
            if ($secretoCat && in_array($secretoCat->id, array_map('intval', $usuarioIds), true)) {
                $secretoUsuarioId = $secretoCat->id;
            }
        }

        return DB::transaction(function () use ($presentacion, $jurados, $secretoUsuarioId) {
            JuradoPresentacion::where('id_presentacion', $presentacion->id)->delete();

            $creadas = [];

            foreach ($jurados as $jurado) {
                $esSecreto = ($secretoUsuarioId !== null && (int) $jurado->id === (int) $secretoUsuarioId);
                $creadas[] = $this->crearAsignacion($presentacion, $jurado, $esSecreto);
            }

            return $creadas;
        });
    }

    public function designarJuradoSecreto(Presentacion $presentacion, int $usuarioId): JuradoPresentacion
    {
        if ($presentacion->esPreseleccion()) {
            throw new \UnexpectedValueException('La etapa de preselección no permite jurado secreto.');
        }

        $asignacion = JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('id_usuario', $usuarioId)
            ->first();

        if (! $asignacion) {
            throw new \UnexpectedValueException('El usuario indicado no está asignado como jurado en esta presentación.');
        }

        return DB::transaction(function () use ($presentacion, $asignacion) {
            // Desmarcar cualquier otro jurado secreto en esta presentación
            JuradoPresentacion::where('id_presentacion', $presentacion->id)
                ->where('id', '!=', $asignacion->id)
                ->update(['es_secreto' => false]);

            $asignacion->update(['es_secreto' => true]);

            return $asignacion;
        });
    }

    public function agregarJurado(Presentacion $presentacion, int $usuarioId, bool $esSecreto = false): JuradoPresentacion
    {
        if ($this->estaCompleta($presentacion)) {
            throw new \UnexpectedValueException(sprintf(
                'La presentación ya tiene los %d jurados requeridos.',
                $this->cantidadRequerida($presentacion)
            ));
        }

        if ($presentacion->esPreseleccion() && $esSecreto) {
            throw new \UnexpectedValueException('La etapa de preselección no permite jurado secreto.');
        }

        $jurado = $this->juradoValido($usuarioId);

        return $this->crearAsignacion($presentacion, $jurado, $esSecreto);
    }

    public function removerJurado(Presentacion $presentacion, int $usuarioId): void
    {
        $cantidad = $this->cantidadAsignada($presentacion);

        if ($cantidad <= $this->cantidadRequerida($presentacion)) {
            throw new \UnexpectedValueException(sprintf(
                'La presentación debe mantener %d jurados; use la edición completa para sustituirlos.',
                $this->cantidadRequerida($presentacion)
            ));
        }

        JuradoPresentacion::where('id_presentacion', $presentacion->id)
            ->where('id_usuario', $usuarioId)
            ->delete();
    }

    private function crearAsignacion(Presentacion $presentacion, Usuario $jurado, bool $esSecreto = false): JuradoPresentacion
    {
        return JuradoPresentacion::firstOrCreate([
            'id_presentacion' => $presentacion->id,
            'id_usuario' => $jurado->id,
        ], [
            'es_secreto' => $esSecreto,
            'es_controlador' => $jurado->puedeControlarTiempo(),
            'estado' => JuradoPresentacionEstadoEnum::ASIGNADO,
        ]);
    }

    /**
     * @return Collection<int, Usuario>
     */
    private function juradosDisponibles(Presentacion $presentacion): Collection
    {
        return Usuario::where('rol', RolEnum::JURADO)
            ->whereDoesntHave('presentacionesJurado', fn ($q) => $q->where('jurado_presentacion.id_presentacion', $presentacion->id))
            ->get()
            ->sortBy(fn (Usuario $jurado) => $jurado->presentacionesJurado()->count())
            ->values();
    }

    /**
     * @param  list<int>  $usuarioIds
     * @return Collection<int, Usuario>
     */
    private function juradosValidos(array $usuarioIds): Collection
    {
        return Usuario::where('rol', RolEnum::JURADO)
            ->whereIn('id', array_map('intval', $usuarioIds))
            ->get();
    }

    private function juradoValido(int $usuarioId): Usuario
    {
        $jurado = Usuario::where('rol', RolEnum::JURADO)->find($usuarioId);

        if ($jurado === null) {
            throw new \UnexpectedValueException('El usuario indicado no es un jurado válido.');
        }

        return $jurado;
    }
}