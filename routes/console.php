<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('festival:reset-presentaciones', function () {
    $this->info('Reiniciando estado de presentaciones...');

    \Illuminate\Support\Facades\DB::transaction(function () {
        \App\Modules\Calificaciones\Models\CalificacionHistorial::query()->delete();
        \App\Modules\Calificaciones\Models\DetalleCalificacion::query()->delete();
        \App\Modules\Calificaciones\Models\Calificacion::query()->delete();

        \App\Modules\Jurados\Models\JuradoPresentacion::query()->update([
            'estado' => \App\Support\Enums\JuradoPresentacionEstadoEnum::ASIGNADO->value,
        ]);

        \App\Modules\Presentaciones\Models\Presentacion::query()->update([
            'estado' => \App\Support\Enums\PresentacionEstadoEnum::POR_PARTICIPAR->value,
            'hora_inicio' => null,
            'hora_fin' => null,
            'cronometro_iniciado_at' => null,
            'cronometro_finalizado_at' => null,
            'nota_final' => null,
        ]);

        \App\Modules\Categorias\Models\Categoria::query()->update([
            'jurado_secreto_revelado' => false,
            'podio_revelado_hasta' => null,
            'revelado_at' => null,
            'finalizada' => false,
            'finalizada_at' => null,
        ]);
    });

    $this->info('Presentaciones y calificaciones reiniciadas con éxito al estado inicial (POR_PARTICIPAR).');
})->purpose('Reiniciar las presentaciones a estado inicial (POR_PARTICIPAR) vaciando calificaciones y cronómetro');
