<?php

namespace App\Modules\Inscripciones\Services;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Inscripciones\Models\InscripcionParticipante;
use App\Modules\Participantes\Models\Participante;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\InscripcionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta el alta manual (CRUD admin) y es reutilizado por la importación
 * de archivos para no duplicar las reglas de validación y persistencia.
 */
class RegistrarInscripcionService
{
    /**
     * Valida una inscripción en formato canónico (igual al de la importación).
     *
     * @param  array<string,mixed>  $datos
     * @return list<string>
     */
    public function validar(array $datos): array
    {
        $errores = [];

        $categorias = $this->mapaNormalizado(Categoria::all(), 'categoria');
        $departamentos = $this->mapaNormalizado(Departamento::all(), 'nombre');

        $categoria = $datos['categoria'] ?? null;

        if ($categoria === null) {
            $errores[] = 'Falta la categoría.';
        } elseif (! isset($categorias[$this->clave($categoria)])) {
            $errores[] = sprintf('La categoría "%s" no existe (verifique el nombre oficial en la convocatoria).', $categoria);
        }

        $fase = strtoupper(trim($datos['fase'] ?? FaseEnum::PRESELECCION->value));

        if (! in_array($fase, [FaseEnum::PRESELECCION->value, FaseEnum::FINAL->value], true)) {
            $errores[] = sprintf('La fase "%s" no es válida (use PRESELECCION o FINAL).', $datos['fase'] ?? '');
        }

        $departamento = $datos['departamento'] ?? null;

        if ($fase === FaseEnum::PRESELECCION->value) {
            if ($departamento === null) {
                $errores[] = 'En preselección es obligatorio indicar el departamento.';
            } elseif (! isset($departamentos[$this->clave($departamento)])) {
                $errores[] = sprintf('El departamento "%s" no existe.', $departamento);
            }
        } elseif ($departamento !== null && ! isset($departamentos[$this->clave($departamento)])) {
            $errores[] = sprintf('El departamento "%s" no existe (en la final es opcional).', $departamento);
        }

        if ($datos['fecha_inscripcion'] !== null && ! $this->esFechaValida((string) $datos['fecha_inscripcion'])) {
            $errores[] = sprintf('La fecha de inscripción "%s" no es válida (formato YYYY-MM-DD).', $datos['fecha_inscripcion']);
        }

        if ($datos['fecha_presentacion'] !== null && ! $this->esFechaValida((string) $datos['fecha_presentacion'])) {
            $errores[] = sprintf('La fecha de presentación "%s" no es válida (formato YYYY-MM-DD).', $datos['fecha_presentacion']);
        }

        if ($datos['hora_presentacion'] !== null && ! $this->esHoraValida((string) $datos['hora_presentacion'])) {
            $errores[] = sprintf('La hora de presentación "%s" no es válida (formato HH:MM).', $datos['hora_presentacion']);
        }

        $integrantes = $datos['integrantes'] ?? [];

        if ($integrantes === []) {
            $errores[] = 'Debe indicar al menos un integrante.';
        }

        $categoriaModelo = $categoria !== null ? ($categorias[$this->clave($categoria)] ?? null) : null;

        if ($categoriaModelo !== null && count($integrantes) > $categoriaModelo->max_participantes) {
            $errores[] = sprintf(
                'La categoría "%s" admite máximo %d integrante(s); se indicaron %d.',
                $categoriaModelo->categoria,
                $categoriaModelo->max_participantes,
                count($integrantes)
            );
        }

        $sinNombre = [];

        foreach ($integrantes as $posicion => $integrante) {
            if (($integrante['nombre'] ?? null) === null) {
                $sinNombre[] = $posicion + 1;
            }
        }

        if ($sinNombre !== []) {
            $errores[] = 'Falta el nombre del integrante '.implode(', ', $sinNombre).'.';
        }

        $clavesIntegrantes = array_map(
            fn ($integrante) => $this->clave(implode('|', array_filter([
                $integrante['nombre'] ?? '',
                $integrante['paterno'] ?? '',
                $integrante['materno'] ?? '',
            ]))),
            $integrantes
        );

        if (array_diff_assoc($clavesIntegrantes, array_unique($clavesIntegrantes)) !== []) {
            $errores[] = 'Hay integrantes repetidos dentro de la misma inscripción.';
        }

        return $errores;
    }

    /**
     * Registra una inscripción nueva con su presentación y sus integrantes
     * (participantes existentes reutilizados por nombre+paterno+materno).
     *
     * @param  array<string,mixed>  $datos
     */
    public function registrar(array $datos): Inscripcion
    {
        $this->comprobarValidez($datos);

        return DB::transaction(fn () => $this->persistir($datos));
    }

    /**
     * Actualiza una inscripción y sincroniza sus integrantes.
     *
     * @param  array<string,mixed>  $datos
     */
    public function actualizar(Inscripcion $inscripcion, array $datos): Inscripcion
    {
        $this->comprobarValidez($datos);

        return DB::transaction(function () use ($inscripcion, $datos) {
            $categorias = $this->mapaNormalizado(Categoria::all(), 'categoria');
            $departamentos = $this->mapaNormalizado(Departamento::all(), 'nombre');

            $categoria = $categorias[$this->clave($datos['categoria'] ?? '')];
            $esFinal = $this->esFinal($datos);
            $departamento = isset($datos['departamento']) && $datos['departamento'] !== null && $datos['departamento'] !== ''
                ? ($departamentos[$this->clave((string) $datos['departamento'])] ?? null)
                : null;
            $fase = $this->resolverFase($esFinal, $departamento);

            $cambiaCategoria = $inscripcion->id_categoria !== $categoria->id;

            $inscripcion->fill([
                'fecha_inscripcion' => $datos['fecha_inscripcion'] ?? now()->toDateString(),
                'hora_presentacion' => $datos['hora_presentacion'],
                'fecha_presentacion' => $datos['fecha_presentacion'],
                'id_fase' => $fase?->id,
                'id_categoria' => $categoria->id,
                'id_departamento' => $departamento?->id,
            ])->save();

            $presentes = [];

            foreach ($datos['integrantes'] as $integrante) {
                $participante = $this->crearOReutilizarParticipante($integrante);
                $presentes[] = $participante->id;

                InscripcionParticipante::firstOrCreate([
                    'id_inscripcion' => $inscripcion->id,
                    'id_participante' => $participante->id,
                ]);
            }

            InscripcionParticipante::where('id_inscripcion', $inscripcion->id)
                ->whereNotIn('id_participante', $presentes)
                ->delete();

            $presentacion = $inscripcion->presentacion;

            if ($presentacion !== null) {
                $presentacion->nombre_presentacion = $datos['nombre_presentacion']
                    ?? $this->nombreCompleto($datos['integrantes'][0] ?? null);

                if ($cambiaCategoria && $presentacion->estado === PresentacionEstadoEnum::POR_PARTICIPAR) {
                    $presentacion->tiempo_maximo = $categoria->tiempoParaFase($fase?->fase);
                }

                $presentacion->save();
            }

            return $inscripcion;
        });
    }

    /**
     * @param  array<string,mixed>  $datos
     */
    private function comprobarValidez(array $datos): void
    {
        $errores = $this->validar($datos);

        if ($errores !== []) {
            throw new \UnexpectedValueException('Los datos de la inscripción no son válidos: '.implode('; ', $errores));
        }
    }

    /**
     * @param  array<string,mixed>  $datos
     */
    private function persistir(array $datos): Inscripcion
    {
        $categorias = $this->mapaNormalizado(Categoria::all(), 'categoria');
        $departamentos = $this->mapaNormalizado(Departamento::all(), 'nombre');

        $categoria = $categorias[$this->clave($datos['categoria'] ?? '')];
        $esFinal = $this->esFinal($datos);
        $departamento = isset($datos['departamento']) && $datos['departamento'] !== null && $datos['departamento'] !== ''
            ? ($departamentos[$this->clave((string) $datos['departamento'])] ?? null)
            : null;
        $fase = $this->resolverFase($esFinal, $departamento);

        $inscripcion = Inscripcion::create([
            'fecha_inscripcion' => $datos['fecha_inscripcion'] ?? now()->toDateString(),
            'hora_presentacion' => $datos['hora_presentacion'],
            'fecha_presentacion' => $datos['fecha_presentacion'],
            'id_fase' => $fase?->id,
            'id_categoria' => $categoria->id,
            'id_departamento' => $departamento?->id,
            'estado' => InscripcionEstadoEnum::REGISTRADA,
        ]);

        foreach ($datos['integrantes'] as $integrante) {
            $participante = $this->crearOReutilizarParticipante($integrante);

            InscripcionParticipante::create([
                'id_inscripcion' => $inscripcion->id,
                'id_participante' => $participante->id,
            ]);
        }

        Presentacion::create([
            'id_inscripcion' => $inscripcion->id,
            'nombre_presentacion' => $datos['nombre_presentacion']
                ?? $this->nombreCompleto($datos['integrantes'][0] ?? null),
            'estado' => PresentacionEstadoEnum::POR_PARTICIPAR,
            'tiempo_maximo' => $categoria->tiempoParaFase($fase?->fase),
        ]);

        return $inscripcion;
    }

    /**
     * @param  array<string,mixed>  $datos
     */
    private function esFinal(array $datos): bool
    {
        return strtoupper(trim($datos['fase'] ?? FaseEnum::PRESELECCION->value)) === FaseEnum::FINAL->value;
    }

    private function resolverFase(bool $esFinal, ?Departamento $departamento): ?Fase
    {
        if ($esFinal) {
            return Fase::where('fase', FaseEnum::FINAL->value)->whereNull('departamento_id')->first();
        }

        return $departamento === null
            ? null
            : Fase::where('fase', FaseEnum::PRESELECCION->value)->where('departamento_id', $departamento->id)->first();
    }

    /**
     * @param  array{nombre:?string,paterno:?string,materno:?string,pseudonimo:?string}  $integrante
     */
    private function crearOReutilizarParticipante(array $integrante): Participante
    {
        $participante = Participante::query()
            ->where('nombre', $integrante['nombre'])
            ->where('paterno', $integrante['paterno'] ?? null)
            ->where('materno', $integrante['materno'] ?? null)
            ->first();

        if ($participante === null) {
            $participante = Participante::create([
                'nombre' => $integrante['nombre'],
                'paterno' => $integrante['paterno'] ?? null,
                'materno' => $integrante['materno'] ?? null,
                'pseudonimo' => $integrante['pseudonimo'] ?? null,
            ]);
        }

        return $participante;
    }

    /**
     * @param  iterable<object>  $items
     * @return array<string,object>
     */
    private function mapaNormalizado(iterable $items, string $campo): array
    {
        $mapa = [];

        foreach ($items as $item) {
            $mapa[$this->clave((string) $item->{$campo})] = $item;
        }

        return $mapa;
    }

    private function clave(string $valor): string
    {
        $normalizado = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', mb_strtolower(trim($valor)));
        $normalizado = $normalizado === false ? mb_strtolower(trim($valor)) : $normalizado;

        return preg_replace('/\s+/', ' ', $normalizado) ?? $normalizado;
    }

    /**
     * @param  array{nombre:?string,paterno:?string,materno:?string,pseudonimo:?string}|null  $integrante
     */
    private function nombreCompleto(?array $integrante): ?string
    {
        if ($integrante === null) {
            return null;
        }

        $nombre = trim(implode(' ', array_filter([
            $integrante['nombre'] ?? '',
            $integrante['paterno'] ?? '',
            $integrante['materno'] ?? '',
        ])));

        return $nombre === '' ? null : $nombre;
    }

    private function esFechaValida(string $valor): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return false;
        }

        [$anio, $mes, $dia] = explode('-', $valor);

        return checkdate((int) $mes, (int) $dia, (int) $anio);
    }

    private function esHoraValida(string $valor): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $valor) === 1;
    }
}
