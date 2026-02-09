<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('n_serie_bateria', 80)->nullable();
            $table->date('dt_inicial_garantia')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn(['n_serie_bateria', 'dt_inicial_garantia']);
        });
    }
};
