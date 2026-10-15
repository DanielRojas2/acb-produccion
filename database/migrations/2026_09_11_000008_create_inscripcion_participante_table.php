<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_participante', function (Blueprint $table) {
            $table->foreignId('id_participante')->constrained('participantes')->cascadeOnDelete();
            $table->foreignId('id_inscripcion')->constrained('inscripciones')->cascadeOnDelete();

            $table->primary(['id_participante', 'id_inscripcion']);
            $table->index('id_inscripcion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_participante');
    }
};
