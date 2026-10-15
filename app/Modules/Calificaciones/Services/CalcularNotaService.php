<?php

namespace App\Modules\Calificaciones\Services;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\CalificacionEstadoEnum;
use InvalidArgumentException;

class CalcularNotaService
{
    /**
     * Valida y calcula la nota total del jurado a partir de los valores de los parámetros.
     *
     * @param  array<int, float|int|string>  $valoresPorParametro  [id_parametro => valor]
     *
     * @throws InvalidArgumentException
     */
    public function calcularNotaJurado(Categoria $categoria, array $valoresPorParametro): float
    {
        $categoriaParametros = $categoria->categoriaParametros()->with('parametro')->get();

        if ($categoriaParametros->isEmpty()) {
            throw new InvalidArgumentException("La categoría [{$categoria->categoria}] no tiene parámetros configurados.");
        }

        $parametrosRequeridos = $categoriaParametros->pluck('id_parametro')->all();
        $parametrosEnviados = array_keys($valoresPorParametro);

        // Validar que no falten parámetros
        $faltantes = array_diff($parametrosRequeridos, $parametrosEnviados);
        if (! empty($faltantes)) {
            throw new InvalidArgumentException('Faltan parámetros requeridos por evaluar.');
        }

        $notaTotal = 0.0;

        foreach ($categoriaParametros as $cp) {
            $idParametro = $cp->id_parametro;

            if (! array_key_exists($idParametro, $valoresPorParametro)) {
                throw new InvalidArgumentException("Falta calificar el parámetro [{$cp->parametro?->parametro}].");
            }

            $rawValor = $valoresPorParametro[$idParametro];

            if (! is_numeric($rawValor)) {
                throw new InvalidArgumentException("El valor para [{$cp->parametro?->parametro}] debe ser numérico.");
            }

            $valor = (float) $rawValor;

            if ($valor < $cp->valor_minimo || $valor > $cp->valor_maximo) {
                throw new InvalidArgumentException(
                    "El valor [{$valor}] para el parámetro [{$cp->parametro?->parametro}] está fuera del rango permitido ({$cp->valor_minimo} - {$cp->valor_maximo})."
                );
            }

            // Suma directa de puntos (el porcentaje ES el puntaje máximo directo)
            $notaTotal += $valor;
        }

        return round($notaTotal, 2);
    }

    /**
     * Calcula la nota final oficial de la presentación como promedio aritmético de las calificaciones emitidas.
     */
    public function calcularNotaFinalPresentacion(Presentacion $presentacion): ?float
    {
        // Cargar los jurados con sus calificaciones emitidas/corregidas
        $calificaciones = $presentacion->jurados()
            ->whereHas('calificacion', function ($query) {
                $query->whereIn('estado', [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO]);
            })
            ->with('calificacion')
            ->get()
            ->pluck('calificacion');

        if ($calificaciones->isEmpty()) {
            return null;
        }

        $suma = $calificaciones->sum('nota_total');
        $cantidad = $calificaciones->count();

        if ($cantidad === 0) {
            return null;
        }

        return round($suma / $cantidad, 2);
    }
}
