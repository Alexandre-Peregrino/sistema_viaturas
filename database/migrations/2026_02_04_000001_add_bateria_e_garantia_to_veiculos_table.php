<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {

            // ✅ Sempre proteger com hasColumn para evitar "Duplicate column"
            if (!Schema::hasColumn('veiculos', 'n_serie_bateria')) {
                $table->string('n_serie_bateria', 80)->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'dt_inicial_garantia')) {
                $table->date('dt_inicial_garantia')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'garantia_bateria_meses')) {
                $table->integer('garantia_bateria_meses')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'dt_final_garantia')) {
                $table->date('dt_final_garantia')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // ✅ Down também deve ser seguro
            if (Schema::hasColumn('veiculos', 'dt_final_garantia')) {
                $table->dropColumn('dt_final_garantia');
            }

            if (Schema::hasColumn('veiculos', 'garantia_bateria_meses')) {
                $table->dropColumn('garantia_bateria_meses');
            }

            if (Schema::hasColumn('veiculos', 'dt_inicial_garantia')) {
                $table->dropColumn('dt_inicial_garantia');
            }

            if (Schema::hasColumn('veiculos', 'n_serie_bateria')) {
                $table->dropColumn('n_serie_bateria');
            }
        });
    }
};
