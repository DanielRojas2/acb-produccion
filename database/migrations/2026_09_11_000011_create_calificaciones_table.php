<?php

use App\Support\Enums\CalificacionEstadoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jurado_presentacion')->unique()->constrained('jurado_presentacion')->cascadeOnDelete();
            $table->decimal('nota_total', 5, 2)->default(0);
            $table->string('estado')->default(CalificacionEstadoEnum::BORRADOR->value);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->timestamp('fecha_modificacion')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
