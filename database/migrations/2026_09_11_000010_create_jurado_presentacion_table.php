<?php

use App\Support\Enums\JuradoPresentacionEstadoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurado_presentacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_presentacion')->constrained('presentaciones')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->boolean('es_secreto')->default(false);
            $table->boolean('es_controlador')->default(false);
            $table->string('estado')->default(JuradoPresentacionEstadoEnum::ASIGNADO->value);
            $table->timestamps();

            $table->unique(['id_presentacion', 'id_usuario']);
            $table->index('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurado_presentacion');
    }
};