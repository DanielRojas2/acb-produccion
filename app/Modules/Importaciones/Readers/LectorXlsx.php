<?php

namespace App\Modules\Importaciones\Readers;

use App\Modules\Importaciones\Exceptions\LecturaException;
use SimpleXMLElement;
use ZipArchive;

class LectorXlsx extends LectorBase implements ArchivoLector
{
    private const NS_SPREADSHEET = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_RELATIONSHIPS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const NS_PACKAGE = 'http://schemas.openxmlformats.org/package/2006/relationships';

    public function leer(string $ruta): array
    {
        $zip = new ZipArchive;

        if ($zip->open($ruta) !== true) {
            throw new LecturaException('No se pudo abrir el archivo XLSX (¿está corrupto o no es un libro válido?).');
        }

        try {
            $hoja = $this->primeraHoja($zip);
            $compartidas = $this->stringCompartidos($zip);

            $contenido = $zip->getFromName($hoja);

            if ($contenido === false) {
                throw new LecturaException('El libro XLSX no contiene hojas de cálculo.');
            }
        } finally {
            $zip->close();
        }

        $xml = $this->xml($contenido);

        $celdasPorFila = [];

        foreach ($xml->xpath('//m:sheetData/m:row') as $filaXml) {
            $celdas = [];

            foreach ($filaXml->children(self::NS_SPREADSHEET) as $celda) {
                $atributos = $celda->attributes();
                $referencia = (string) ($atributos['r'] ?? '');
                $columna = $this->indiceColumna($referencia);

                if ($columna === null) {
                    continue;
                }

                $tipo = (string) ($atributos['t'] ?? '');
                $celdas[$columna] = $this->valorCelda($celda, $tipo, $compartidas);
            }

            $celdasPorFila[] = $celdas;
        }

        return $this->convertirFilas($celdasPorFila);
    }

    /**
     * @param  list<array<int,string|null>>  $celdasPorFila
     * @return list<array<string,mixed>>
     */
    private function convertirFilas(array $celdasPorFila): array
    {
        $cabeceras = null;
        $columnas = [];
        $filas = [];

        foreach ($celdasPorFila as $celdas) {
            if ($celdas === []) {
                continue;
            }

            if ($cabeceras === null) {
                ksort($celdas);
                $columnas = array_keys($celdas);
                $cabeceras = array_values(array_map(
                    fn ($valor) => $this->normalizarClave($celdas[$valor] ?? ''),
                    $columnas
                ));
                $this->validarCabeceras($cabeceras);

                continue;
            }

            $mapeadas = [];

            foreach ($columnas as $indice => $columna) {
                if (isset($celdas[$columna]) && $celdas[$columna] !== '') {
                    $mapeadas[$cabeceras[$indice]] = $celdas[$columna];
                }
            }

            $fila = $this->normalizarFila($mapeadas);

            if (! $this->filaVacia($fila)) {
                $filas[] = $fila;
            }
        }

        if ($filas === []) {
            throw new LecturaException('El archivo XLSX no contiene filas de datos.');
        }

        return $filas;
    }

    private function xml(string $contenido): SimpleXMLElement
    {
        $xml = simplexml_load_string($contenido);

        if (! $xml instanceof SimpleXMLElement) {
            throw new LecturaException('No se pudo interpretar el contenido del archivo XLSX.');
        }

        $xml->registerXPathNamespace('m', self::NS_SPREADSHEET);

        return $xml;
    }

    private function primeraHoja(ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');

        if ($workbook === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $xml = $this->xml($workbook);

        $hojas = $xml->xpath('//m:sheets/m:sheet');

        if ($hojas === false || $hojas === []) {
            return 'xl/worksheets/sheet1.xml';
        }

        $rid = (string) ($hojas[0]->attributes(self::NS_RELATIONSHIPS)['id'] ?? 'rId1');

        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($rels !== false) {
            $xmlRels = $this->xml($rels);

            foreach ($xmlRels->xpath('//*[local-name()="Relationship"]') as $relacion) {
                $atributos = $relacion->attributes();

                if ((string) ($atributos['Id'] ?? '') !== $rid) {
                    continue;
                }

                $destino = (string) ($atributos['Target'] ?? '');

                if ($destino !== '') {
                    return str_starts_with($destino, 'xl/') ? $destino : 'xl/'.$destino;
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * @return list<string>
     */
    private function stringCompartidos(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $recibidos = $this->xml($xml);
        $compartidos = [];

        foreach ($recibidos->xpath('//m:si') as $si) {
            $texto = '';

            $si->registerXPathNamespace('m', self::NS_SPREADSHEET);

            foreach ($si->xpath('.//m:t') as $fragmento) {
                $texto .= (string) $fragmento;
            }

            $compartidos[] = $texto;
        }

        return $compartidos;
    }

    /**
     * @param  list<string>  $compartidos
     */
    private function valorCelda(SimpleXMLElement $celda, string $tipo, array $compartidos): ?string
    {
        $valorCifrado = (string) ($celda->v ?? '');

        return match ($tipo) {
            's' => (string) ($compartidos[(int) $valorCifrado] ?? ''),
            'inlineStr' => $this->textoInlineStrings($celda),
            'b' => $valorCifrado === '1' ? '1' : '0',
            'e' => null,
            default => $valorCifrado === '' ? null : $valorCifrado,
        };
    }

    private function textoInlineStrings(SimpleXMLElement $celda): string
    {
        $texto = '';

        if (isset($celda->is)) {
            foreach ($celda->is->t as $fragmento) {
                $texto .= (string) $fragmento;
            }
        }

        return $texto;
    }

    private function indiceColumna(string $referencia): ?int
    {
        if (! preg_match('/^([A-Z]+)/', $referencia, $coincidencias)) {
            return null;
        }

        $indice = 0;

        foreach (str_split($coincidencias[1]) as $letra) {
            $indice = $indice * 26 + (ord($letra) - ord('A') + 1);
        }

        return $indice - 1;
    }
}
