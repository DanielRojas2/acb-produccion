<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_parametro', function (Blueprint $table) {
            $table->foreignId('id_categoria')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('id_parametro')->constrained('parametros')->cascadeOnDelete();
            $table->unsignedInteger('valor_minimo')->default(1);
            $table->unsignedInteger('valor_maximo');
            $table->unsignedTinyInteger('orden');

            $table->primary(['id_categoria', 'id_parametro']);
            $table->index('id_parametro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_parametro');
    }
};
