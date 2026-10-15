<?php

use App\Support\Enums\PresentacionEstadoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_inscripcion')->unique()->constrained('inscripciones')->cascadeOnDelete();
            $table->string('nombre_presentacion')->nullable();
            $table->string('estado')->default(PresentacionEstadoEnum::POR_PARTICIPAR->value);
            $table->unsignedInteger('tiempo_maximo')->default(5);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->timestamp('cronometro_iniciado_at')->nullable();
            $table->timestamp('cronometro_finalizado_at')->nullable();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('tiempo_maximo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentaciones');
    }
};
