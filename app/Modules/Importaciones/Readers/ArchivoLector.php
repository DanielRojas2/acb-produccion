<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\LecturaException;

interface ArchivoLector
{
    /**
     * Lee un archivo y retorna filas normalizadas listas para validar.
     *
     * @return list<array{categoria:?string,departamento:?string,fase:?string,fecha_inscripcion:?string,fecha_presentacion:?string,hora_presentacion:?string,nombre_presentacion:?string,integrantes:list<array{nombre:?string,paterno:?string,materno:?string,pseudonimo:?string}>}>
     *
     * @throws LecturaException
     */
    public function leer(string $ruta): array;
}
