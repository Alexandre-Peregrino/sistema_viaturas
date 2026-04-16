<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Garante que não exista mais de uma lotação aberta para o mesmo veículo
        DB::statement("
            CREATE UNIQUE INDEX veiculo_lotacoes_unica_aberta
            ON veiculo_lotacoes (veiculo_id)
            WHERE data_saida IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_unica_aberta");
    }
};
