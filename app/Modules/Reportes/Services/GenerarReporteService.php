<?php

namespace App\Modules\Reportes\Services;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Presentaciones\Models\Presentacion;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerarReporteService
{
    /**
     * Obtiene el listado consolidado de resultados y ranking ordenado para una categoría y fase.
     *
     * @return array{
     *     fase: Fase,
     *     categoria: Categoria,
     *     departamento: ?Departamento,
     *     items: array<int, array<string, mixed>>,
     *     jurados_cabecera: array<int, string>,
     *     total_participantes: int,
     *     promedio_general: float
     * }
     */
    public function obtenerResultados(Fase $fase, Categoria $categoria, ?int $departamentoId = null): array
    {
        $query = Presentacion::with([
            'inscripcion.participantes',
            'inscripcion.departamento',
            'inscripcion.fase',
            'juradosPresentacion.usuario',
            'juradosPresentacion.calificacion',
        ])
        ->whereHas('inscripcion', function ($q) use ($fase, $categoria, $departamentoId) {
            $q->where('id_fase', $fase->id)
              ->where('id_categoria', $categoria->id);

            if ($departamentoId) {
                $q->where('id_departamento', $departamentoId);
            }
        });

        $presentaciones = $query->get();

        // Ordenar por nota final descendente (las no calificadas al final)
        $ordenadas = $presentaciones->sort(function (Presentacion $a, Presentacion $b) {
            $notaA = $a->nota_final !== null ? (float) $a->nota_final : -1.0;
            $notaB = $b->nota_final !== null ? (float) $b->nota_final : -1.0;

            if ($notaA === $notaB) {
                return $a->id <=> $b->id;
            }

            return $notaB <=> $notaA;
        })->values();

        // Identificar nombres de jurados únicos para las columnas
        $juradosCabecera = [];
        foreach ($ordenadas as $pres) {
            foreach ($pres->juradosPresentacion as $jp) {
                $nombreJurado = $jp->usuario ? $jp->usuario->nombreCompleto() : "Jurado #{$jp->id_usuario}";
                if (! in_array($nombreJurado, $juradosCabecera, true)) {
                    $juradosCabecera[] = $nombreJurado;
                }
            }
        }

        $items = [];
        $posicion = 1;
        $prevNota = null;
        $puestoEfectivo = 1;

        $sumaNotasFinales = 0;
        $evaluadasCount = 0;

        foreach ($ordenadas as $index => $pres) {
            $notaFinal = $pres->nota_final !== null ? (float) $pres->nota_final : null;

            if ($notaFinal !== null) {
                if ($prevNota !== null && $notaFinal < $prevNota) {
                    $puestoEfectivo = $posicion;
                }
                $prevNota = $notaFinal;
                $sumaNotasFinales += $notaFinal;
                $evaluadasCount++;
            } else {
                $puestoEfectivo = null;
            }

            // Mapear calificaciones por jurado
            $votosJurados = [];
            foreach ($pres->juradosPresentacion as $jp) {
                $nomJurado = $jp->usuario ? $jp->usuario->nombreCompleto() : "Jurado #{$jp->id_usuario}";
                $cal = $jp->calificacion;
                $votosJurados[$nomJurado] = [
                    'id_usuario' => $jp->id_usuario,
                    'jurado_nombre' => $nomJurado,
                    'nota_total' => $cal ? (float) $cal->nota_total : null,
                    'es_secreto' => (bool) $jp->es_secreto,
                    'estado_calificacion' => $cal?->estado?->value ?? 'PENDIENTE',
                ];
            }

            $items[] = [
                'posicion' => $puestoEfectivo,
                'posicion_correlativa' => $posicion,
                'presentacion' => $pres,
                'id_presentacion' => $pres->id,
                'nombre_presentacion' => $pres->nombreMostrado(),
                'integrantes' => $pres->inscripcion?->participantes->pluck('nombre_completo')->all() ?? [],
                'departamento' => $pres->inscripcion?->departamento?->nombre ?? 'Nacional',
                'votos_jurados' => $votosJurados,
                'nota_final' => $notaFinal,
                'estado' => $pres->estado?->value ?? 'POR_PARTICIPAR',
                'estado_label' => $pres->estado?->label() ?? 'Por Participar',
            ];

            $posicion++;
        }

        $promedioGeneral = $evaluadasCount > 0 ? round($sumaNotasFinales / $evaluadasCount, 2) : 0.0;
        $depto = $departamentoId ? Departamento::find($departamentoId) : null;

        return [
            'fase' => $fase,
            'categoria' => $categoria,
            'departamento' => $depto,
            'items' => $items,
            'jurados_cabecera' => $juradosCabecera,
            'total_participantes' => count($items),
            'promedio_general' => $promedioGeneral,
        ];
    }

    /**
     * Obtiene la matriz detallada (sábana) cruzando presentación x jurado x parámetro evaluado.
     */
    public function obtenerSabanaDetallada(Fase $fase, Categoria $categoria, ?int $departamentoId = null): array
    {
        $parametros = $categoria->categoriaParametros()->with('parametro')->orderBy('orden')->get();

        $query = Presentacion::with([
            'inscripcion.participantes',
            'inscripcion.departamento',
            'juradosPresentacion.usuario',
            'juradosPresentacion.calificacion.detalles',
        ])
        ->whereHas('inscripcion', function ($q) use ($fase, $categoria, $departamentoId) {
            $q->where('id_fase', $fase->id)
              ->where('id_categoria', $categoria->id);

            if ($departamentoId) {
                $q->where('id_departamento', $departamentoId);
            }
        });

        $presentaciones = $query->get()->sortByDesc(fn ($p) => $p->nota_final ?? -1)->values();

        $filas = [];
        foreach ($presentaciones as $pres) {
            $juradosDetalle = [];
            foreach ($pres->juradosPresentacion as $jp) {
                $detallesMap = [];
                if ($jp->calificacion) {
                    foreach ($jp->calificacion->detalles as $det) {
                        $detallesMap[$det->id_categoria_parametro] = (float) $det->valor;
                    }
                }

                $juradosDetalle[] = [
                    'jurado_id' => $jp->id_usuario,
                    'jurado_nombre' => $jp->usuario ? $jp->usuario->nombreCompleto() : "Jurado #{$jp->id_usuario}",
                    'es_secreto' => (bool) $jp->es_secreto,
                    'nota_total' => $jp->calificacion ? (float) $jp->calificacion->nota_total : null,
                    'parametros_valores' => $detallesMap,
                ];
            }

            $filas[] = [
                'presentacion' => $pres,
                'nombre' => $pres->nombreMostrado(),
                'departamento' => $pres->inscripcion?->departamento?->nombre ?? 'Nacional',
                'nota_final' => $pres->nota_final !== null ? (float) $pres->nota_final : null,
                'jurados' => $juradosDetalle,
            ];
        }

        $depto = $departamentoId ? Departamento::find($departamentoId) : null;

        return [
            'fase' => $fase,
            'categoria' => $categoria,
            'departamento' => $depto,
            'parametros' => $parametros,
            'filas' => $filas,
        ];
    }

    /**
     * Genera un archivo CSV descargable con los resultados oficiales de la categoría.
     */
    public function exportarCsvResultados(Fase $fase, Categoria $categoria, ?int $departamentoId = null): StreamedResponse
    {
        $data = $this->obtenerResultados($fase, $categoria, $departamentoId);
        $faseStr = $fase->fase instanceof \App\Support\Enums\FaseEnum ? $fase->fase->value : (string) $fase->fase;
        $faseLabel = $fase->fase instanceof \App\Support\Enums\FaseEnum ? $fase->fase->label() : (string) $fase->fase;

        $filename = sprintf(
            'ACB2026_Resultados_%s_%s_%s.csv',
            str_replace(' ', '_', $faseStr),
            str_replace(' ', '_', $categoria->categoria),
            date('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data, $faseLabel) {
            $handle = fopen('php://output', 'w');
            // Escribir BOM UTF-8 para apertura correcta en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado del reporte
            fputcsv($handle, ['XXXIII FESTIVAL NACIONAL DE LA CANCIÓN BOLIVIANA "AQUÍ... CANTA BOLIVIA"'], ';');
            fputcsv($handle, ['ACTA Y REPORTE DE RESULTADOS OFICIALES'], ';');
            fputcsv($handle, ['Fase:', $faseLabel], ';');
            fputcsv($handle, ['Categoría:', $data['categoria']->categoria], ';');
            fputcsv($handle, ['Departamento:', $data['departamento']?->nombre ?? 'TODOS / NACIONAL'], ';');
            fputcsv($handle, ['Fecha de Emisión:', date('d/m/Y H:i:s')], ';');
            fputcsv($handle, [], ';');

            // Cabecera de la tabla
            $cabecera = ['Puesto', 'ID', 'Presentación / Participante', 'Departamento', 'Estado'];
            foreach ($data['jurados_cabecera'] as $jurNom) {
                $cabecera[] = $jurNom;
            }
            $cabecera[] = 'Nota Final';
            fputcsv($handle, $cabecera, ';');

            // Filas
            foreach ($data['items'] as $item) {
                $fila = [
                    $item['posicion'] !== null ? "{$item['posicion']}°" : 'S/C',
                    $item['id_presentacion'],
                    $item['nombre_presentacion'],
                    $item['departamento'],
                    $item['estado_label'],
                ];

                foreach ($data['jurados_cabecera'] as $jurNom) {
                    $voto = $item['votos_jurados'][$jurNom] ?? null;
                    if ($voto && $voto['nota_total'] !== null) {
                        $fila[] = number_format($voto['nota_total'], 2);
                    } else {
                        $fila[] = '-';
                    }
                }

                $fila[] = $item['nota_final'] !== null ? number_format($item['nota_final'], 2) : 'S/C';
                fputcsv($handle, $fila, ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Genera un archivo CSV con la sábana detallada por parámetro.
     */
    public function exportarCsvSabana(Fase $fase, Categoria $categoria, ?int $departamentoId = null): StreamedResponse
    {
        $sabana = $this->obtenerSabanaDetallada($fase, $categoria, $departamentoId);
        $faseStr = $fase->fase instanceof \App\Support\Enums\FaseEnum ? $fase->fase->value : (string) $fase->fase;
        $faseLabel = $fase->fase instanceof \App\Support\Enums\FaseEnum ? $fase->fase->label() : (string) $fase->fase;

        $filename = sprintf(
            'ACB2026_Sabana_Parametros_%s_%s_%s.csv',
            str_replace(' ', '_', $faseStr),
            str_replace(' ', '_', $categoria->categoria),
            date('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($sabana, $faseLabel) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['XXXIII FESTIVAL NACIONAL DE LA CANCIÓN BOLIVIANA "AQUÍ... CANTA BOLIVIA"'], ';');
            fputcsv($handle, ['SÁBANA DE EVALUACIÓN DETALLADA POR PARÁMETRO'], ';');
            fputcsv($handle, ['Fase:', $faseLabel], ';');
            fputcsv($handle, ['Categoría:', $sabana['categoria']->categoria], ';');
            fputcsv($handle, ['Fecha de Emisión:', date('d/m/Y H:i:s')], ';');
            fputcsv($handle, [], ';');

            // Cabeceras
            $cabecera = ['ID Presentación', 'Participante', 'Departamento', 'Jurado Calificador'];
            foreach ($sabana['parametros'] as $param) {
                $cabecera[] = sprintf('%s (Máx %s pts)', $param->parametro?->parametro, number_format($param->valor_maximo, 0));
            }
            $cabecera[] = 'Nota Total Jurado';
            $cabecera[] = 'Nota Final Presentación';
            fputcsv($handle, $cabecera, ';');

            foreach ($sabana['filas'] as $fila) {
                if (empty($fila['jurados'])) {
                    $row = [
                        $fila['presentacion']->id,
                        $fila['nombre'],
                        $fila['departamento'],
                        'Sin jurados asignados',
                    ];
                    foreach ($sabana['parametros'] as $p) {
                        $row[] = '-';
                    }
                    $row[] = '-';
                    $row[] = $fila['nota_final'] !== null ? number_format($fila['nota_final'], 2) : '-';
                    fputcsv($handle, $row, ';');
                } else {
                    foreach ($fila['jurados'] as $jur) {
                        $row = [
                            $fila['presentacion']->id,
                            $fila['nombre'],
                            $fila['departamento'],
                            $jur['jurado_nombre'].($jur['es_secreto'] ? ' [Secreto]' : ''),
                        ];

                        foreach ($sabana['parametros'] as $p) {
                            $val = $jur['parametros_valores'][$p->id] ?? null;
                            $row[] = $val !== null ? number_format($val, 2) : '-';
                        }

                        $row[] = $jur['nota_total'] !== null ? number_format($jur['nota_total'], 2) : '-';
                        $row[] = $fila['nota_final'] !== null ? number_format($fila['nota_final'], 2) : '-';
                        fputcsv($handle, $row, ';');
                    }
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
