<?php

namespace App\Modules\Calificaciones\Services;

use App\Modules\Calificaciones\Events\CalificacionRegistrada;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Calificaciones\Models\DetalleCalificacion;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\JuradoPresentacionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use DomainException;
use Illuminate\Support\Facades\DB;

class RegistrarCalificacionService
{
    public function __construct(
        protected CalcularNotaService $calcularNotaService
    ) {}

    /**
     * Registra oficialmente la calificación de un jurado para una presentación.
     *
     * @param  array<int, float|int|string>  $valoresPorParametro  [id_parametro => valor]
     *
     * @throws DomainException
     */
    public function registrar(JuradoPresentacion $juradoPresentacion, array $valoresPorParametro, Usuario $usuario): Calificacion
    {
        // 1. Validar autorización
        if ($juradoPresentacion->id_usuario !== $usuario->id && ! $usuario->esAdmin()) {
            throw new DomainException('No tiene autorización para registrar la calificación de este jurado.');
        }

        $presentacion = $juradoPresentacion->presentacion;

        if (! $presentacion) {
            throw new DomainException('La presentación asignada no existe.');
        }

        // 2. Validar estado de la presentación (debe estar PARTICIPANDO o FINALIZADO)
        $estadosPermitidos = [
            PresentacionEstadoEnum::PARTICIPANDO,
            PresentacionEstadoEnum::FINALIZADO,
        ];

        if (! in_array($presentacion->estado, $estadosPermitidos, true)) {
            throw new DomainException("No se puede registrar una calificación cuando la presentación está en estado [{$presentacion->estado?->label()}].");
        }

        $categoria = $presentacion->inscripcion?->categoria;

        if (! $categoria) {
            throw new DomainException('La presentación no tiene una categoría asociada.');
        }

        if ($categoria->estaFinalizada()) {
            throw new DomainException("Las calificaciones para la categoría [{$categoria->categoria}] han sido cerradas. No se permiten nuevos registros.");
        }

        return DB::transaction(function () use ($juradoPresentacion, $presentacion, $categoria, $valoresPorParametro) {
            // Verificar si ya existe calificación emitida
            $calificacion = Calificacion::firstOrNew([
                'id_jurado_presentacion' => $juradoPresentacion->id,
            ]);

            if ($calificacion->exists && $calificacion->esEmitida()) {
                throw new DomainException('Este jurado ya emitió su calificación. Para modificarla use la opción de corrección.');
            }

            // Calcular nota total validando rangos de parámetros
            $notaTotal = $this->calcularNotaService->calcularNotaJurado($categoria, $valoresPorParametro);

            $calificacion->nota_total = $notaTotal;
            $calificacion->estado = CalificacionEstadoEnum::EMITIDO;
            $calificacion->fecha_inicio = $calificacion->fecha_inicio ?? now();
            $calificacion->fecha_finalizacion = now();
            $calificacion->save();

            // Guardar o actualizar detalles por parámetro
            foreach ($valoresPorParametro as $idParametro => $valor) {
                DetalleCalificacion::updateOrCreate(
                    [
                        'id_calificacion' => $calificacion->id,
                        'id_parametro' => (int) $idParametro,
                    ],
                    [
                        'valor' => (float) $valor,
                    ]
                );
            }

            // Actualizar estado del jurado en la presentación
            $juradoPresentacion->estado = JuradoPresentacionEstadoEnum::FINALIZADO;
            $juradoPresentacion->save();

            // Recalcular nota final de la presentación
            $presentacion->refresh();
            $notaFinal = $this->calcularNotaService->calcularNotaFinalPresentacion($presentacion);
            $presentacion->nota_final = $notaFinal;
            $presentacion->save();

            // Emitir eventos en tiempo real
            try {
                event(new CalificacionRegistrada($calificacion->load(['juradoPresentacion.usuario', 'juradoPresentacion.presentacion', 'detalles'])));

                if ($categoria) {
                    $rankingService = app(\App\Modules\Rankings\Services\CalcularRankingService::class);
                    $ranking = $rankingService->rankingPorCategoria($categoria);
                    event(new \App\Modules\Rankings\Events\RankingActualizado($categoria, $ranking));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error transmitiendo evento de calificación registrada: '.$e->getMessage());
            }

            return $calificacion;
        });
    }
}
