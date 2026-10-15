<?php

namespace App\Modules\Calificaciones\Services;

use App\Modules\Calificaciones\Events\CalificacionCorregida;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\CalificacionHistorial;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use DomainException;
use Illuminate\Support\Facades\DB;

class CorregirCalificacionService
{
    public function __construct(
        protected CalcularNotaService $calcularNotaService
    ) {}

    /**
     * Corrige una calificación existente, recalculando notas y dejando registro en auditoría.
     *
     * @param  array<int, float|int|string>  $nuevosValoresPorParametro  [id_parametro => nuevo_valor]
     *
     * @throws DomainException
     */
    public function corregir(Calificacion $calificacion, array $nuevosValoresPorParametro, Usuario $usuario, ?string $motivo = null): Calificacion
    {
        $juradoPresentacion = $calificacion->juradoPresentacion;

        if (! $juradoPresentacion) {
            throw new DomainException('No se encontró la asignación de jurado para esta calificación.');
        }

        // 1. Validar autorización: el mismo jurado o un admin
        if ($juradoPresentacion->id_usuario !== $usuario->id && ! $usuario->esAdmin()) {
            throw new DomainException('No tiene autorización para modificar esta calificación.');
        }

        $presentacion = $juradoPresentacion->presentacion;

        if (! $presentacion) {
            throw new DomainException('La presentación asociada no existe.');
        }

        // 2. Solo se permite corregir cuando la presentación está en FINALIZADO
        if ($presentacion->estado !== PresentacionEstadoEnum::FINALIZADO) {
            throw new DomainException('Solo es posible corregir una calificación cuando la presentación ha finalizado.');
        }

        $categoria = $presentacion->inscripcion?->categoria;

        if (! $categoria) {
            throw new DomainException('La presentación no tiene una categoría asociada.');
        }

        if ($categoria->estaFinalizada()) {
            throw new DomainException("Las calificaciones para la categoría [{$categoria->categoria}] han sido cerradas. No se permiten modificaciones ni correcciones.");
        }

        return DB::transaction(function () use ($calificacion, $juradoPresentacion, $presentacion, $categoria, $nuevosValoresPorParametro, $usuario, $motivo) {
            // Cargar detalles anteriores para snapshot
            $detallesAnteriores = $calificacion->detalles()->get();
            $snapshotAnterior = [
                'nota_total' => $calificacion->nota_total,
                'detalles' => $detallesAnteriores->map(fn ($d) => [
                    'id_parametro' => $d->id_parametro,
                    'valor' => $d->valor,
                ])->values()->all(),
            ];

            // Validar y calcular la nueva nota total
            $nuevaNotaTotal = $this->calcularNotaService->calcularNotaJurado($categoria, $nuevosValoresPorParametro);

            // Actualizar la calificación
            $calificacion->nota_total = $nuevaNotaTotal;
            $calificacion->estado = CalificacionEstadoEnum::CORREGIDO;
            $calificacion->fecha_modificacion = now();
            $calificacion->save();

            // Actualizar detalles de parámetros
            $snapshotNuevoDetalles = [];
            foreach ($nuevosValoresPorParametro as $idParametro => $valor) {
                DetalleCalificacion::updateOrCreate(
                    [
                        'id_calificacion' => $calificacion->id,
                        'id_parametro' => (int) $idParametro,
                    ],
                    [
                        'valor' => (float) $valor,
                    ]
                );

                $snapshotNuevoDetalles[] = [
                    'id_parametro' => (int) $idParametro,
                    'valor' => (float) $valor,
                ];
            }

            $snapshotNuevo = [
                'nota_total' => $nuevaNotaTotal,
                'detalles' => $snapshotNuevoDetalles,
            ];

            // Registrar en historial de auditoría
            CalificacionHistorial::create([
                'id_calificacion' => $calificacion->id,
                'id_usuario' => $usuario->id,
                'datos_anteriores' => $snapshotAnterior,
                'datos_nuevos' => $snapshotNuevo,
                'motivo' => $motivo,
                'fecha' => now(),
            ]);

            // Recalcular nota final de la presentación
            $presentacion->refresh();
            $notaFinal = $this->calcularNotaService->calcularNotaFinalPresentacion($presentacion);
            $presentacion->nota_final = $notaFinal;
            $presentacion->save();

            // Emitir eventos en tiempo real
            try {
                event(new CalificacionCorregida($calificacion->load(['juradoPresentacion.usuario', 'juradoPresentacion.presentacion', 'detalles'])));

                if ($categoria) {
                    $rankingService = app(\App\Modules\Rankings\Services\CalcularRankingService::class);
                    $ranking = $rankingService->rankingPorCategoria($categoria);
                    event(new \App\Modules\Rankings\Events\RankingActualizado($categoria, $ranking));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error transmitiendo evento de calificación corregida: '.$e->getMessage());
            }

            return $calificacion;
        });
    }
}
