<?php

use App\Support\Enums\InscripcionEstadoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inscripcion');
            $table->time('hora_presentacion')->nullable();
            $table->date('fecha_presentacion')->nullable();
            $table->foreignId('id_fase')->constrained('fases')->restrictOnDelete();
            $table->foreignId('id_categoria')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('id_departamento')->nullable()->constrained('departamentos')->restrictOnDelete();
            $table->string('estado')->default(InscripcionEstadoEnum::REGISTRADA->value);
            $table->timestamps();

            $table->index(['id_fase', 'id_categoria']);
            $table->index('id_departamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
