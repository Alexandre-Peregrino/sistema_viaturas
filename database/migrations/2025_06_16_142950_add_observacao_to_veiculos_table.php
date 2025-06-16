<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adiciona a coluna 'status' à tabela 'veiculos'.
     */
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Adiciona a coluna 'quilometragem' como um inteiro grande (bigInteger) e torna-a anulável (nullable).
            // Certifique-se de que esta linha foi executada com sucesso antes.
            if (!Schema::hasColumn('veiculos', 'quilometragem')) {
                $table->bigInteger('quilometragem')->nullable()->after('entrega_dados_opm'); // Ajuste a posição se necessário
            }

            // Adiciona a coluna 'observacao' como texto e a define como 'nullable'.
            // Ela será adicionada após a coluna 'quilometragem'.
            if (!Schema::hasColumn('veiculos', 'observacao')) {
                $table->text('observacao')->nullable()->after('quilometragem');
            }

            // Adiciona a coluna 'status' como string e a define como 'nullable'.
            // Ela será adicionada após a coluna 'observacao'.
            // Se você sempre espera um valor para 'status' e já validou isso, pode remover ->nullable().
            if (!Schema::hasColumn('veiculos', 'status')) {
                $table->string('status', 255)->nullable()->after('observacao');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Remove as colunas 'status' e 'observacao' da tabela 'veiculos' em caso de rollback.
     */
    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('veiculos', 'observacao')) {
                $table->dropColumn('observacao');
            }
            if (Schema::hasColumn('veiculos', 'quilometragem')) {
                $table->dropColumn('quilometragem');
            }
        });
    }
};
