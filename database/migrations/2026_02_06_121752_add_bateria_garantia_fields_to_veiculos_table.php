<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Bateria / Garantia
            if (!Schema::hasColumn('veiculos', 'dt_inicial_garantia')) {
                $table->date('dt_inicial_garantia')->nullable()->after('entrega_dados_opm');
            }

            if (!Schema::hasColumn('veiculos', 'garantia_bateria_meses')) {
                $table->smallInteger('garantia_bateria_meses')->nullable()->after('dt_inicial_garantia');
            }

            if (!Schema::hasColumn('veiculos', 'dt_final_garantia')) {
                $table->date('dt_final_garantia')->nullable()->after('garantia_bateria_meses');
            }

            if (!Schema::hasColumn('veiculos', 'n_serie_bateria')) {
                $table->string('n_serie_bateria', 80)->nullable()->after('dt_final_garantia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'n_serie_bateria')) {
                $table->dropColumn('n_serie_bateria');
            }
            if (Schema::hasColumn('veiculos', 'dt_final_garantia')) {
                $table->dropColumn('dt_final_garantia');
            }
            if (Schema::hasColumn('veiculos', 'garantia_bateria_meses')) {
                $table->dropColumn('garantia_bateria_meses');
            }
            if (Schema::hasColumn('veiculos', 'dt_inicial_garantia')) {
                $table->dropColumn('dt_inicial_garantia');
            }
        });
    }
};
