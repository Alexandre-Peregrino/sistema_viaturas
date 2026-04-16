<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculos', 'opm_id')) {
                $table->unsignedBigInteger('opm_id')->nullable()->index();
            }
        });

        // FK em passo separado para evitar erro se coluna já existia sem FK
        Schema::table('veiculos', function (Blueprint $table) {
            // cria FK somente se a coluna existir
            if (Schema::hasColumn('veiculos', 'opm_id')) {
                // evita “duplicate constraint” em ambientes onde a FK já exista
                // (Laravel não tem hasForeign, então criamos com nome fixo e
                // você só precisará ajustar se já existir com o mesmo nome)
                try {
                    $table->foreign('opm_id', 'veiculos_opm_id_fk')
                        ->references('id')
                        ->on('opms')
                        ->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignora se já existir
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'opm_id')) {
                try {
                    $table->dropForeign('veiculos_opm_id_fk');
                } catch (\Throwable $e) {
                    // ignora se não existir com esse nome
                }

                // se você não quer remover a coluna no down, pode comentar
                $table->dropColumn('opm_id');
            }
        });
    }
};
