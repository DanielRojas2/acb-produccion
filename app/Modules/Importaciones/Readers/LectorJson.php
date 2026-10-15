<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\LecturaException;

class LectorJson extends LectorBase implements ArchivoLector
{
    private const BOM = "\xEF\xBB\xBF";

    public function leer(string $ruta): array
    {
        $contenido = file_get_contents($ruta);

        if ($contenido === false) {
            throw new LecturaException('No se pudo leer el archivo JSON.');
        }

        if (str_starts_with($contenido, self::BOM)) {
            $contenido = substr($contenido, strlen(self::BOM));
        }

        $decodificado = json_decode($contenido, true);

        if (! is_array($decodificado)) {
            throw new LecturaException('El JSON no es válido: '.json_last_error_msg().'.');
        }

        $filas = array_is_list($decodificado)
            ? $decodificado
            : ($decodificado['filas'] ?? null);

        if (! is_array($filas)) {
            throw new LecturaException('Estructura JSON no reconocida: use un arreglo de objetos o {"filas": [ ... ]}.');
        }

        $resultado = [];

        foreach ($filas as $elemento) {
            if (! is_array($elemento)) {
                throw new LecturaException('Cada elemento del JSON debe ser un objeto con los campos de la inscripción.');
            }

            $celdas = [];

            foreach ($elemento as $clave => $valor) {
                if (is_array($valor)) {
                    continue;
                }

                $celdas[$this->normalizarClave((string) $clave)] = is_string($valor) ? trim($valor) : $valor;
            }

            $fila = $this->normalizarFila($celdas);

            if (is_array($elemento['integrantes'] ?? null)) {
                $integrantes = [];

                foreach ($elemento['integrantes'] as $integrante) {
                    if (! is_array($integrante)) {
                        continue;
                    }

                    $normalizado = [
                        'nombre' => isset($integrante['nombre']) ? trim((string) $integrante['nombre']) : null,
                        'paterno' => isset($integrante['paterno']) ? trim((string) $integrante['paterno']) : null,
                        'materno' => isset($integrante['materno']) ? trim((string) $integrante['materno']) : null,
                        'pseudonimo' => isset($integrante['pseudonimo']) ? trim((string) $integrante['pseudonimo']) : null,
                    ];

                    if (! $this->integranteVacio($normalizado)) {
                        $integrantes[] = $normalizado;
                    }
                }

                if ($integrantes !== []) {
                    $fila['integrantes'] = $integrantes;
                }
            }

            if (! $this->filaVacia($fila)) {
                $resultado[] = $fila;
            }
        }

        if ($resultado === []) {
            throw new LecturaException('El archivo JSON no contiene filas de datos.');
        }

        return $resultado;
    }
}
