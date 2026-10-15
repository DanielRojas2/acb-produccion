<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->boolean('jurado_secreto_revelado')->default(false)->after('tiempo_presentacion');
            $table->timestamp('revelado_at')->nullable()->after('jurado_secreto_revelado');
            $table->boolean('finalizada')->default(false)->after('revelado_at');
            $table->timestamp('finalizada_at')->nullable()->after('finalizada');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn(['jurado_secreto_revelado', 'revelado_at', 'finalizada', 'finalizada_at']);
        });
    }
};
