<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_calificacion')->constrained('calificaciones')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->json('datos_anteriores');
            $table->json('datos_nuevos');
            $table->string('motivo')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();

            $table->index('id_calificacion');
            $table->index('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion_historial');
    }
};
