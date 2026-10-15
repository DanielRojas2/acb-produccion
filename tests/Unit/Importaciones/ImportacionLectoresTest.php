<?php

namespace Tests\Unit\Importaciones;

use App\Modules\Importaciones\Exceptions\FormatoNoSoportadoException;
use App\Modules\Importaciones\Exceptions\LecturaException;
use App\Modules\Importaciones\Readers\FabricaLectores;
use App\Modules\Importaciones\Readers\LectorCsv;
use App\Modules\Importaciones\Readers\LectorJson;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImportacionLectoresTest extends TestCase
{
    private function archivoTemporal(string $contenido, string $nombre): string
    {
        $ruta = sys_get_temp_dir().'/'.$nombre;
        file_put_contents($ruta, $contenido);

        return $ruta;
    }

    #[Test]
    public function lee_csv_con_punto_y_coma_y_quita_bom(): void
    {
        $ruta = $this->archivoTemporal(
            "\xEF\xBB\xBFcategoria;departamento;fase;integrante_1_nombre;integrante_1_paterno\n"
            ."Ñusta;Oruro;PRESELECCION;Ana;García\n",
            'csv_semicolon_bom.csv'
        );

        $filas = (new LectorCsv)->leer($ruta);

        $this->assertCount(1, $filas);
        $this->assertSame('Ñusta', $filas[0]['categoria']);
        $this->assertSame('Oruro', $filas[0]['departamento']);
        $this->assertSame('Ana', $filas[0]['integrantes'][0]['nombre']);
        $this->assertSame('García', $filas[0]['integrantes'][0]['paterno']);
    }

    #[Test]
    public function normaliza_cabeceras_con_acentos(): void
    {
        $ruta = $this->archivoTemporal(
            "categoría;fecha de presentación;nombre de presentación;integrante_1_nombre\n"
            ."Ñusta;2026-10-05;Camila;Ana\n",
            'csv_acentos.csv'
        );

        $filas = (new LectorCsv)->leer($ruta);

        $this->assertSame('Ñusta', $filas[0]['categoria']);
        $this->assertSame('2026-10-05', $filas[0]['fecha_presentacion']);
        $this->assertSame('Camila', $filas[0]['nombre_presentacion']);
    }

    #[Test]
    public function detecta_delimitador_de_coma(): void
    {
        $ruta = $this->archivoTemporal(
            "categoria,departamento,fase,integrante_1_nombre\n"
            ."Solista vocal infantil (con o sin acompañamiento),La Paz,PRESELECCION,Lucía\n",
            'csv_coma.csv'
        );

        $filas = (new LectorCsv)->leer($ruta);

        $this->assertSame('Solista vocal infantil (con o sin acompañamiento)', $filas[0]['categoria']);
        $this->assertSame('Lucía', $filas[0]['integrantes'][0]['nombre']);
    }

    #[Test]
    public function reordena_integrantes_e_ignora_posiciones_vacias(): void
    {
        $ruta = $this->archivoTemporal(
            "categoria;departamento;integrante_1_nombre;integrante_1_paterno;integrante_2_nombre;integrante_3_nombre\n"
            ."Dúo vocal instrumental;Oruro;Primero;Uno;;Tercero\n",
            'csv_integrantes.csv'
        );

        $filas = (new LectorCsv)->leer($ruta);

        $this->assertCount(2, $filas[0]['integrantes']);
        $this->assertSame('Primero', $filas[0]['integrantes'][0]['nombre']);
        $this->assertSame('Tercero', $filas[0]['integrantes'][1]['nombre']);
    }

    #[Test]
    public function lanza_error_si_no_reconoce_columnas(): void
    {
        $this->expectException(LecturaException::class);

        $ruta = $this->archivoTemporal("columna1;columna2\nvalor1;valor2\n", 'csv_inexperto.csv');
        (new LectorCsv)->leer($ruta);
    }

    #[Test]
    public function lee_json_con_integrantes_anidados(): void
    {
        $ruta = $this->archivoTemporal(
            '{"filas":[{"categoria":"Trío vocal instrumental","departamento":"Cochabamba",'.
            '"fase":"PRESELECCION","nombre_presentacion":"Trío CBBA","integrantes":['.
            '{"nombre":"Ana","paterno":"Cruz"},{"nombre":"Beto","paterno":"Lima"}]}]}',
            'json_anidado.json'
        );

        $filas = (new LectorJson)->leer($ruta);

        $this->assertCount(1, $filas);
        $this->assertSame('Trío CBBA', $filas[0]['nombre_presentacion']);
        $this->assertCount(2, $filas[0]['integrantes']);
        $this->assertSame('Ana', $filas[0]['integrantes'][0]['nombre']);
    }

    #[Test]
    public function lanza_error_si_json_es_invalido(): void
    {
        $this->expectException(LecturaException::class);

        $ruta = $this->archivoTemporal('{esto no es json', 'json_malo.json');
        (new LectorJson)->leer($ruta);
    }

    #[Test]
    public function fabrica_rechaza_formato_no_soportado(): void
    {
        $this->expectException(FormatoNoSoportadoException::class);

        (new FabricaLectores)->crear('pdf');
    }
}
