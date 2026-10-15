<?php

namespace App\Modules\Importaciones\Services;

use App\Modules\Importaciones\Exceptions\FormatoNoSoportadoException;
use App\Modules\Importaciones\Exceptions\LecturaException;
use App\Modules\Importaciones\Readers\FabricaLectores;
use App\Modules\Importaciones\ValueObjects\ResultadoImportacion;
use App\Modules\Inscripciones\Services\RegistrarInscripcionService;
use App\Modules\Participantes\Models\Participante;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportarInscripcionesService
{
    public function __construct(
        private readonly FabricaLectores $fabricaLectores,
        private readonly RegistrarInscripcionService $registro,
    ) {}

    public function importar(UploadedFile|string $archivo): ResultadoImportacion
    {
        $resultado = new ResultadoImportacion;

        $ruta = $archivo instanceof UploadedFile ? $archivo->getPathname() : $archivo;
        $extension = $archivo instanceof UploadedFile
            ? $archivo->getClientOriginalExtension()
            : pathinfo($archivo, PATHINFO_EXTENSION);

        try {
            $lector = $this->fabricaLectores->crear($extension);
        } catch (FormatoNoSoportadoException $excepcion) {
            return $resultado->establecerErrorGeneral($excepcion->getMessage());
        }

        try {
            $filas = $lector->leer($ruta);
        } catch (LecturaException $excepcion) {
            return $resultado->establecerErrorGeneral($excepcion->getMessage());
        }

        $resultado->filasProcesadas = count($filas);

        $this->validarFilas($filas, $resultado);

        if ($resultado->tieneErrores()) {
            return $resultado;
        }

        try {
            DB::transaction(function () use ($filas, $resultado) {
                foreach ($filas as $fila) {
                    $baseParticipantes = Participante::count();

                    $this->registro->registrar($fila);

                    $resultado->inscripcionesCreadas++;
                    $resultado->participantesCreados += Participante::count() - $baseParticipantes;
                    $resultado->presentacionesCreadas++;
                }
            });
        } catch (\Throwable $excepcion) {
            $fallo = new ResultadoImportacion;

            $fallo->filasProcesadas = count($filas);

            return $fallo->establecerErrorGeneral('No se pudo guardar la importación: '.$excepcion->getMessage());
        }

        return $resultado->marcarExitoso();
    }

    /**
     * @param  list<array<string,mixed>>  $filas
     */
    private function validarFilas(array $filas, ResultadoImportacion $resultado): void
    {
        $vistas = [];

        foreach ($filas as $indice => $fila) {
            $numero = $indice + 1;

            foreach ($this->registro->validar($fila) as $mensaje) {
                $resultado->agregarError($numero, $mensaje);
            }

            $clave = $this->claveInscripcion($fila);

            if (isset($vistas[$clave])) {
                $resultado->agregarError($numero, sprintf('Inscripción repetida (idéntica a la fila %d).', $vistas[$clave]));
            } else {
                $vistas[$clave] = $numero;
            }
        }
    }

    /**
     * @param  array<string,mixed>  $fila
     */
    private function claveInscripcion(array $fila): string
    {
        $integrantes = array_map(
            fn ($integrante) => implode('|', array_filter([
                $integrante['nombre'] ?? '',
                $integrante['paterno'] ?? '',
                $integrante['materno'] ?? '',
            ])),
            $fila['integrantes']
        );

        sort($integrantes);

        return $this->clave(implode('§', [
            $fila['categoria'] ?? '',
            $fila['departamento'] ?? '',
            $fila['fase'] ?? '',
            $fila['fecha_presentacion'] ?? '',
            $fila['hora_presentacion'] ?? '',
            $fila['nombre_presentacion'] ?? '',
            implode('§', $integrantes),
        ]));
    }

    private function clave(string $valor): string
    {
        $normalizado = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', mb_strtolower(trim($valor)));
        $normalizado = $normalizado === false ? mb_strtolower(trim($valor)) : $normalizado;

        return preg_replace('/\s+/', ' ', $normalizado) ?? $normalizado;
    }
}
