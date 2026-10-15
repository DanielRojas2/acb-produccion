<?php

namespace App\Modules\Importaciones\Controllers;

use App\Modules\Importaciones\Services\ImportarInscripcionesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportacionController
{
    public function index(): View
    {
        return view('admin.importaciones.index', [
            'resultado' => session('resultado_importacion'),
        ]);
    }

    public function importar(Request $request, ImportarInscripcionesService $servicio): RedirectResponse
    {
        $validados = $request->validate([
            'archivo' => ['required', 'file'],
        ]);

        /** @var UploadedFile $archivo */
        $archivo = $validados['archivo'];

        $resultado = $servicio->importar($archivo);

        return redirect()->route('admin.importaciones.index')
            ->with('resultado_importacion', $resultado);
    }

    public function plantilla(): StreamedResponse
    {
        $cabeceras = $this->cabecerasPlantilla(3);

        $filas = [
            [
                'Solista vocal (con o sin acompañamiento)', 'Chuquisaca', 'PRESELECCION',
                '2026-09-11', '2026-10-05', '19:00', '',
                'Camila', 'Mamani', 'Rivera', '',
                '', '', '', '',
            ],
            [
                'Dúo vocal instrumental', 'La Paz', 'PRESELECCION',
                '2026-09-11', '2026-10-06', '20:15', 'Dúo Andino',
                'Juan', 'Pérez', 'Mamani', 'Chojlla',
                'María', 'López', 'Quispe', '',
            ],
        ];

        $lineas = [$this->lineaCSV($cabeceras)];
        foreach ($filas as $fila) {
            $lineas[] = $this->lineaCSV($fila);
        }

        $csv = implode('', $lineas);

        return response()->streamDownload(
            fn () => print ($csv),
            'plantilla_importacion.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * @return list<mixed>
     */
    private function cabecerasPlantilla(int $integranteMaximo): array
    {
        $cabeceras = [
            'categoria', 'departamento', 'fase', 'fecha_inscripcion',
            'fecha_presentacion', 'hora_presentacion', 'nombre_presentacion',
        ];

        foreach (range(1, $integranteMaximo) as $numero) {
            foreach (['nombre', 'paterno', 'materno', 'pseudonimo'] as $campo) {
                $cabeceras[] = sprintf('integrante_%d_%s', $numero, $campo);
            }
        }

        return $cabeceras;
    }

    /**
     * @param  list<mixed>  $valores
     */
    private function lineaCSV(array $valores): string
    {
        return implode(';', array_map(fn ($valor) => $this->celdaCSV($valor), $valores))."\r\n";
    }

    private function celdaCSV(mixed $valor): string
    {
        $valor = (string) $valor;

        if (strpbrk($valor, ';",'."\n") !== false) {
            $valor = '"'.str_replace('"', '""', $valor).'"';
        }

        return $valor;
    }
}
