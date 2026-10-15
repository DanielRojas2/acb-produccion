<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('paterno')->nullable();
            $table->string('materno')->nullable();
            $table->string('pseudonimo')->nullable();
            $table->timestamps();

            $table->index(['nombre', 'paterno', 'materno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};
