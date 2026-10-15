<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\FormatoNoSoportadoException;

class FabricaLectores
{
    public function crear(string $extension): ArchivoLector
    {
        return match (strtolower($extension)) {
            'csv', 'txt' => new LectorCsv,
            'json' => new LectorJson,
            'xlsx', 'xlsm' => new LectorXlsx,
            default => throw new FormatoNoSoportadoException(
                sprintf('Formato ".%s" no soportado. Utilice CSV, JSON o XLSX.', $extension)
            ),
        };
    }
}
