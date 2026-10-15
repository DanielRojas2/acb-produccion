<?php

namespace App\Modules\Rankings\Services;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Fases\Models\Fase;
use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\CalificacionEstadoEnum;
use App\Support\Enums\FaseEnum;
use Illuminate\Support\Collection;

class CalcularRankingService
{
    /**
     * Calcula y retorna el ranking de una categoría o general, ordenado por nota final descendente.
     * Enmascara la identidad y calificaciones del jurado secreto para la vista pública
     * si la categoría no ha sido revelada.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rankingPorCategoria(?Categoria $categoria = null, ?Fase $fase = null, bool $esPublico = true, ?int $departamentoId = null): Collection
    {
        $faseEnum = $fase?->fase ?? FaseEnum::FINAL;
        $estaRevelada = $categoria ? $categoria->estaRevelada() : false;

        $query = Presentacion::query()
            ->select('presentaciones.*')
            ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
            ->join('fases', 'inscripciones.id_fase', '=', 'fases.id')
            ->where('fases.fase', $faseEnum->value);

        if ($categoria !== null) {
            $query->where('inscripciones.id_categoria', $categoria->id);
        }

        if ($departamentoId !== null && $departamentoId > 0) {
            $query->where('inscripciones.id_departamento', $departamentoId);
        }

        $presentaciones = $query->with([
                'inscripcion.categoria',
                'inscripcion.participantes',
                'inscripcion.departamento',
                'jurados.usuario',
                'jurados.calificacion.detalles.parametro',
            ])
            ->orderByRaw('presentaciones.nota_final IS NULL, presentaciones.nota_final DESC')
            ->orderBy('presentaciones.id')
            ->get();

        $items = collect();

        foreach ($presentaciones as $presentacion) {
            $catPresentacion = $presentacion->inscripcion?->categoria;
            $catRevelada = $catPresentacion ? $catPresentacion->estaRevelada() : $estaRevelada;
            $catFinalizada = (bool) ($catPresentacion?->finalizada ?? false);

            // Si es público y la categoría aún NO está revelada/finalizada,
            // la nota en puntaje final no contempla la nota del jurado secreto
            if ($esPublico && ! $catRevelada && $presentacion->jurados->contains('es_secreto', true)) {
                $juradosVisibles = $presentacion->jurados->filter(function (JuradoPresentacion $jp) {
                    if ((bool) $jp->es_secreto) {
                        return false;
                    }
                    $cal = $jp->calificacion;
                    return $cal !== null && in_array($cal->estado, [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO], true) && $cal->nota_total !== null;
                });

                $tieneVotoSecreto = $presentacion->jurados->contains(function (JuradoPresentacion $jp) {
                    return (bool) $jp->es_secreto && $jp->calificacion !== null && in_array($jp->calificacion->estado, [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO], true);
                });

                if ($juradosVisibles->isNotEmpty()) {
                    $notaFinal = round($juradosVisibles->sum(fn ($jp) => (float) $jp->calificacion->nota_total) / $juradosVisibles->count(), 2);
                } elseif (! $tieneVotoSecreto && $presentacion->nota_final !== null) {
                    $notaFinal = (float) $presentacion->nota_final;
                } else {
                    $notaFinal = null;
                }
            } else {
                $notaFinal = $presentacion->nota_final !== null ? (float) $presentacion->nota_final : null;
            }

            // Desglose de jurados
            $juradosData = $presentacion->jurados->map(function (JuradoPresentacion $jp) use ($esPublico, $catRevelada) {
                $usuario = $jp->usuario;
                $calificacion = $jp->calificacion;
                $esSecreto = (bool) $jp->es_secreto;
                $ocultar = $esPublico && $esSecreto && ! $catRevelada;

                return [
                    'jurado_id' => $ocultar ? null : $usuario?->id,
                    'jurado_nombre' => $ocultar ? 'Jurado Secreto' : ($usuario ? "{$usuario->nombre} {$usuario->paterno}" : 'Jurado'),
                    'es_secreto' => $esSecreto,
                    'es_controlador' => (bool) $jp->es_controlador,
                    'es_revelado' => ! $ocultar,
                    'calificado' => $calificacion !== null && in_array($calificacion->estado, [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO], true),
                    'nota_total' => $ocultar ? null : ($calificacion?->nota_total !== null ? (float) $calificacion->nota_total : null),
                    'detalles' => $ocultar ? [] : ($calificacion?->detalles->map(fn ($d) => [
                        'parametro' => $d->parametro?->parametro,
                        'valor' => (float) $d->valor,
                    ])->all() ?? []),
                ];
            });

            $tieneJuradoSecreto = $presentacion->jurados->contains('es_secreto', true);
            $juradosCalificados = $presentacion->jurados->filter(fn ($jp) => $jp->calificacion && in_array($jp->calificacion->estado, [CalificacionEstadoEnum::EMITIDO, CalificacionEstadoEnum::CORREGIDO], true))->count();

            $items->push([
                'id' => $presentacion->id,
                'nombre' => $presentacion->nombreMostrado(),
                'categoria' => $catPresentacion?->categoria ?? '—',
                'categoria_id' => $catPresentacion?->id,
                'departamento' => $presentacion->inscripcion?->departamento?->nombre,
                'departamento_id' => $presentacion->inscripcion?->id_departamento,
                'participantes' => $presentacion->inscripcion?->participantes->pluck('nombre_completo')->all() ?? [],
                'foto_url' => $presentacion->fotoUrl(),
                'estado' => $presentacion->estado?->value,
                'estado_label' => $presentacion->estado?->label(),
                'nota_final' => $notaFinal,
                'categoria_finalizada' => $catFinalizada,
                'categoria_revelada' => $catRevelada,
                'tiene_jurado_secreto' => $tieneJuradoSecreto,
                'jurados_calificados_count' => $juradosCalificados,
                'jurados_total_count' => $presentacion->jurados->count(),
                'jurados' => $juradosData,
            ]);
        }

        // Ordenar por nota_final descendente y asignar posición
        $sorted = $items->sort(function ($a, $b) {
            if ($a['nota_final'] === null && $b['nota_final'] === null) {
                return $a['id'] <=> $b['id'];
            }
            if ($a['nota_final'] === null) {
                return 1;
            }
            if ($b['nota_final'] === null) {
                return -1;
            }
            if ($a['nota_final'] === $b['nota_final']) {
                return $a['id'] <=> $b['id'];
            }
            return $b['nota_final'] <=> $a['nota_final'];
        })->values();

        $ranking = collect();
        $posicion = 1;
        $notaAnterior = null;
        $posicionReal = 1;

        foreach ($sorted as $item) {
            $nota = $item['nota_final'];
            if ($nota !== null) {
                if ($notaAnterior !== null && $nota < $notaAnterior) {
                    $posicion = $posicionReal;
                }
                $notaAnterior = $nota;
                $posicionReal++;
                $item['posicion'] = $posicion;
            } else {
                $item['posicion'] = '—';
            }
            $ranking->push($item);
        }

        return $ranking;
    }

    /**
     * Retorna conteo de presentaciones agrupado por departamento para los filtros del sidebar.
     *
     * @return Collection<int, array{id: int, nombre: string, total: int}>
     */
    public function conteoDepartamentos(?int $categoriaId = null, ?Fase $fase = null): Collection
    {
        $faseEnum = $fase?->fase ?? FaseEnum::FINAL;

        $departamentos = \App\Modules\Departamentos\Models\Departamento::orderBy('nombre')->get();

        return $departamentos->map(function ($depto) use ($categoriaId, $faseEnum) {
            $query = Presentacion::query()
                ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
                ->join('fases', 'inscripciones.id_fase', '=', 'fases.id')
                ->where('fases.fase', $faseEnum->value)
                ->where('inscripciones.id_departamento', $depto->id);

            if ($categoriaId !== null && $categoriaId > 0) {
                $query->where('inscripciones.id_categoria', $categoriaId);
            }

            return [
                'id' => $depto->id,
                'nombre' => $depto->nombre,
                'total' => $query->count(),
            ];
        });
    }

    /**
     * Retorna conteo de presentaciones agrupado por categoría para los filtros del sidebar.
     *
     * @return Collection<int, array{id: int, nombre: string, total: int, finalizada: bool, revelada: bool}>
     */
    public function conteoCategorias(?int $departamentoId = null, ?Fase $fase = null): Collection
    {
        $faseEnum = $fase?->fase ?? FaseEnum::FINAL;

        $categorias = Categoria::orderBy('id')->get();

        return $categorias->map(function (Categoria $cat) use ($departamentoId, $faseEnum) {
            $query = Presentacion::query()
                ->join('inscripciones', 'presentaciones.id_inscripcion', '=', 'inscripciones.id')
                ->join('fases', 'inscripciones.id_fase', '=', 'fases.id')
                ->where('fases.fase', $faseEnum->value)
                ->where('inscripciones.id_categoria', $cat->id);

            if ($departamentoId !== null && $departamentoId > 0) {
                $query->where('inscripciones.id_departamento', $departamentoId);
            }

            return [
                'id' => $cat->id,
                'nombre' => $cat->categoria,
                'total' => $query->count(),
                'finalizada' => (bool) $cat->finalizada,
                'revelada' => (bool) $cat->estaRevelada(),
            ];
        });
    }

    /**
     * Retorna el resumen del estado de todas las categorías en la Etapa Final.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function resumenCategoriasEtapaFinal(): Collection
    {
        $categorias = Categoria::with('juradoSecreto')->orderBy('id')->get();

        return $categorias->map(function (Categoria $cat) {
            $asignacionSecreta = JuradoPresentacion::where('es_secreto', true)
                ->whereHas('presentacion.inscripcion', function ($query) use ($cat) {
                    $query->where('id_categoria', $cat->id)
                        ->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value));
                })
                ->with('usuario')
                ->first();

            $juradoSecreto = $cat->juradoSecreto ?? $asignacionSecreta?->usuario;

            $presentaciones = Presentacion::whereHas('inscripcion', function ($query) use ($cat) {
                $query->where('id_categoria', $cat->id)
                    ->whereHas('fase', fn ($f) => $f->where('fase', FaseEnum::FINAL->value));
            })->get();

            $total = $presentaciones->count();
            $finalizadas = $presentaciones->where('estado', \App\Support\Enums\PresentacionEstadoEnum::FINALIZADO)->count();
            $conNotaFinal = $presentaciones->whereNotNull('nota_final')->count();

            $top3 = [];
            if ($total > 0 && ($cat->estaRevelada() || $cat->estaFinalizada() || $finalizadas > 0)) {
                $rankingCol = $this->rankingPorCategoria($cat, null, true);
                $top3 = $rankingCol->take(3)->values()->map(function ($item) {
                    return [
                        'id' => $item['id'] ?? null,
                        'posicion' => $item['posicion'] ?? null,
                        'nombre' => $item['nombre'] ?? '',
                        'departamento' => $item['departamento'] ?? '',
                        'nota_final' => $item['nota_final'] ?? null,
                    ];
                })->all();
            }

            return [
                'categoria' => $cat,
                'id' => $cat->id,
                'nombre' => $cat->categoria,
                'jurado_secreto' => $juradoSecreto,
                'jurado_secreto_revelado' => (bool) $cat->jurado_secreto_revelado,
                'revelado_at' => $cat->revelado_at,
                'finalizada' => (bool) $cat->finalizada,
                'finalizada_at' => $cat->finalizada_at,
                'total_presentaciones' => $total,
                'presentaciones_finalizadas' => $finalizadas,
                'presentaciones_con_nota' => $conNotaFinal,
                'todas_evaluadas' => $total > 0 && $finalizadas === $total,
                'top3' => $top3,
            ];
        });
    }
}
