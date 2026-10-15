<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\LecturaException;
use Illuminate\Support\Str;

abstract class LectorBase
{
    /** @var array<string,string> Alias de cabeceras a claves canónicas. */
    protected array $correspondencia = [
        'categoria' => 'categoria',
        'departamento' => 'departamento',
        'fase' => 'fase',
        'fecha_inscripcion' => 'fecha_inscripcion',
        'fecha_de_inscripcion' => 'fecha_inscripcion',
        'fecha_presentacion' => 'fecha_presentacion',
        'fecha_de_presentacion' => 'fecha_presentacion',
        'hora_presentacion' => 'hora_presentacion',
        'hora_de_presentacion' => 'hora_presentacion',
        'nombre_presentacion' => 'nombre_presentacion',
        'nombre_de_presentacion' => 'nombre_presentacion',
        'nombre_artistico' => 'nombre_presentacion',
    ];

    protected function normalizarTexto(string $valor): string
    {
        return Str::ascii(mb_strtolower(trim($valor)));
    }

    protected function normalizarClave(string $valor): string
    {
        $clave = $this->normalizarTexto($valor);
        $clave = preg_replace('/[^a-z0-9]+/', '_', $clave) ?? $clave;

        return trim($clave, '_');
    }

    /**
     * @param  array<string,string|null>  $celdas
     * @return array<string,mixed>
     */
    protected function normalizarFila(array $celdas): array
    {
        $fila = [
            'categoria' => null,
            'departamento' => null,
            'fase' => null,
            'fecha_inscripcion' => null,
            'fecha_presentacion' => null,
            'hora_presentacion' => null,
            'nombre_presentacion' => null,
            'integrantes' => [],
        ];

        $integrantes = [];

        foreach ($celdas as $clave => $valor) {
            if ($valor === null || trim((string) $valor) === '') {
                continue;
            }

            $valor = trim((string) $valor);

            if (isset($this->correspondencia[$clave])) {
                $fila[$this->correspondencia[$clave]] = $valor;

                continue;
            }

            if (preg_match('/^(?:integrante|participante)_(\d+)_(nombre|paterno|materno|pseudonimo)$/', $clave, $coincidencias)) {
                $integrantes[(int) $coincidencias[1]][$coincidencias[2]] = $valor;
            }
        }

        ksort($integrantes);

        foreach ($integrantes as $integrante) {
            $integrante = array_intersect_key($integrante, array_flip(['nombre', 'paterno', 'materno', 'pseudonimo']));

            if ($this->integranteVacio($integrante)) {
                continue;
            }

            $fila['integrantes'][] = [
                'nombre' => $integrante['nombre'] ?? null,
                'paterno' => $integrante['paterno'] ?? null,
                'materno' => $integrante['materno'] ?? null,
                'pseudonimo' => $integrante['pseudonimo'] ?? null,
            ];
        }

        return $fila;
    }

    /**
     * @param  array<string,mixed>  $integrante
     */
    protected function integranteVacio(array $integrante): bool
    {
        return empty($integrante['nombre'])
            && empty($integrante['paterno'])
            && empty($integrante['materno'])
            && empty($integrante['pseudonimo']);
    }

    /**
     * @param  array<string,mixed>  $fila
     */
    protected function filaVacia(array $fila): bool
    {
        $campos = array_diff_key($fila, ['integrantes' => true]);

        return array_filter($campos, fn ($valor) => $valor !== null) === [] && $fila['integrantes'] === [];
    }

    /**
     * @param  list<string>  $cabeceras
     */
    protected function columnasReconocidas(array $cabeceras): bool
    {
        foreach ($cabeceras as $clave) {
            if (isset($this->correspondencia[$clave])) {
                return true;
            }

            if (preg_match('/^(?:integrante|participante)_\d+_(nombre|paterno|materno|pseudonimo)$/', $clave)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $cabeceras
     */
    protected function validarCabeceras(array $cabeceras): void
    {
        if (! $this->columnasReconocidas($cabeceras)) {
            throw new LecturaException(
                'No se reconocieron columnas del archivo. Utilice la plantilla de ejemplo (categoria, departamento, fase, integrante_1_nombre, ...).'
            );
        }
    }
}
