<?php

namespace App\Modules\Importaciones\ValueObjects;

class ResultadoImportacion
{
    public bool $exitoso = false;

    public ?string $errorGeneral = null;

    public int $filasProcesadas = 0;

    public int $inscripcionesCreadas = 0;

    public int $participantesCreados = 0;

    public int $presentacionesCreadas = 0;

    /** @var list<array{fila:int|string,mensaje:string}> */
    public array $errores = [];

    public function tieneErrores(): bool
    {
        return $this->errorGeneral !== null || $this->errores !== [];
    }

    public function marcarExitoso(): static
    {
        $this->exitoso = true;

        return $this;
    }

    public function establecerErrorGeneral(string $mensaje): static
    {
        $this->errorGeneral = $mensaje;

        return $this;
    }

    public function agregarError(int|string $fila, string $mensaje): static
    {
        $this->errores[] = ['fila' => $fila, 'mensaje' => $mensaje];

        return $this;
    }

    public function establecerContadores(int $inscripcionesCreadas, int $participantesCreados, int $presentacionesCreadas): static
    {
        $this->inscripcionesCreadas = $inscripcionesCreadas;
        $this->participantesCreados = $participantesCreados;
        $this->presentacionesCreadas = $presentacionesCreadas;

        return $this;
    }
}
