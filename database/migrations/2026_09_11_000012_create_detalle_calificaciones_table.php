<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_calificacion')->constrained('calificaciones')->cascadeOnDelete();
            $table->foreignId('id_parametro')->constrained('parametros')->cascadeOnDelete();
            $table->decimal('valor', 5, 2);
            $table->timestamps();

            $table->unique(['id_calificacion', 'id_parametro']);
            $table->index('id_parametro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_calificaciones');
    }
};
