<?php

namespace Tests\Support;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\FaseEnum;
use App\Support\Enums\InscripcionEstadoEnum;
use App\Support\Enums\PresentacionEstadoEnum;
use App\Support\Enums\RolEnum;

trait CreaPresentacion
{
    protected function presentacionPreseleccion(): Presentacion
    {
        return $this->crearPresentacion(FaseEnum::PRESELECCION, 'Solista vocal (con o sin acompañamiento)', 'Oruro');
    }

    protected function presentacionFinal(): Presentacion
    {
        return $this->crearPresentacion(FaseEnum::FINAL, 'Solista vocal (con o sin acompañamiento)', null);
    }

    protected function presentacionEstudio(string $categoriaNombre = 'Solista vocal (con o sin acompañamiento)'): Presentacion
    {
        return $this->crearPresentacion(FaseEnum::PRESELECCION, $categoriaNombre, 'Oruro');
    }

    protected function crearPresentacion(
        FaseEnum $faseEnum,
        string $categoriaNombre,
        ?string $departamentoNombre
    ): Presentacion {
        $categoria = Categoria::where('categoria', $categoriaNombre)->firstOrFail();

        $departamento = $departamentoNombre !== null
            ? Departamento::where('nombre', $departamentoNombre)->firstOrFail()
            : null;

        $fase = $faseEnum->esPreseleccion()
            ? Fase::where('fase', FaseEnum::PRESELECCION->value)
                ->where('departamento_id', $departamento->id)
                ->firstOrFail()
            : Fase::where('fase', FaseEnum::FINAL->value)
                ->whereNull('departamento_id')
                ->firstOrFail();

        $inscripcion = Inscripcion::create([
            'fecha_inscripcion' => '2026-09-01',
            'fecha_presentacion' => '2026-11-20',
            'hora_presentacion' => '19:00',
            'id_fase' => $fase->id,
            'id_categoria' => $categoria->id,
            'id_departamento' => $departamento?->id,
            'estado' => InscripcionEstadoEnum::REGISTRADA,
        ]);

        return Presentacion::create([
            'id_inscripcion' => $inscripcion->id,
            'nombre_presentacion' => 'Presentación de prueba',
            'estado' => PresentacionEstadoEnum::POR_PARTICIPAR,
            'tiempo_maximo' => $categoria->tiempo_presentacion,
        ]);
    }

    /**
     * @return list<Usuario>
     */
    protected function crearJurados(int $cantidad): array
    {
        return collect(range(1, $cantidad))
            ->map(fn () => Usuario::factory()->create(['rol' => RolEnum::JURADO]))
            ->all();
    }
}