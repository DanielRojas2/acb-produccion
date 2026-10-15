<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\LecturaException;

class LectorCsv extends LectorBase implements ArchivoLector
{
    private const BOM = "\xEF\xBB\xBF";

    public function leer(string $ruta): array
    {
        $contenido = file_get_contents($ruta);

        if ($contenido === false) {
            throw new LecturaException('No se pudo leer el archivo CSV.');
        }

        if (str_starts_with($contenido, self::BOM)) {
            $contenido = substr($contenido, strlen(self::BOM));
        }

        $manejo = fopen('php://temp/maxmemory:10485760', 'r+');
        fwrite($manejo, $contenido);
        rewind($manejo);

        $delimitador = $this->detectarDelimitador($contenido);

        $cabeceras = null;
        $filas = [];

        while (($registro = fgetcsv($manejo, 0, $delimitador, '"', '\\')) !== false) {
            if ($registro === [null] || $registro === ['']) {
                continue;
            }

            if ($cabeceras === null) {
                $cabeceras = array_values(array_map(fn ($v) => $this->normalizarClave((string) $v), $registro));
                $this->validarCabeceras($cabeceras);

                continue;
            }

            $celdas = $this->mapear($cabeceras, $registro);
            $fila = $this->normalizarFila($celdas);

            if (! $this->filaVacia($fila)) {
                $filas[] = $fila;
            }
        }

        fclose($manejo);

        if ($filas === []) {
            throw new LecturaException('El archivo CSV no contiene filas de datos.');
        }

        return $filas;
    }

    private function detectarDelimitador(string $contenido): string
    {
        $lineas = preg_split('/\r\n|\r|\n/', $contenido) ?: [];
        $lineas = array_slice($lineas, 0, 5);

        $mejor = ';';
        $mejorPuntaje = 0;

        foreach ([';', ',', "\t", '|'] as $delimitador) {
            $puntaje = 0;

            foreach ($lineas as $linea) {
                $campos = str_getcsv($linea, $delimitador, '"', '\\');

                if (count($campos) > 1) {
                    $puntaje += count($campos);
                }
            }

            if ($puntaje > $mejorPuntaje) {
                $mejor = $delimitador;
                $mejorPuntaje = $puntaje;
            }
        }

        return $mejor;
    }

    /**
     * @param  list<string>  $cabeceras
     * @param  list<mixed>  $registro
     * @return array<string,string|null>
     */
    private function mapear(array $cabeceras, array $registro): array
    {
        $celdas = [];

        foreach ($cabeceras as $indice => $clave) {
            $valor = $registro[$indice] ?? null;
            $valor = is_string($valor) ? trim($valor) : $valor;
            $celdas[$clave] = $valor === '' ? null : $valor;
        }

        return $celdas;
    }
}
