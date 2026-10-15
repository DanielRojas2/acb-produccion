<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participantes', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('pseudonimo');
        });

        Schema::table('presentaciones', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('nombre_presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('participantes', function (Blueprint $table) {
            $table->dropColumn('foto');
        });

        Schema::table('presentaciones', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
