<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fases', function (Blueprint $table) {
            $table->id();
            $table->string('fase');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();

            $table->index('fase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fases');
    }
};
