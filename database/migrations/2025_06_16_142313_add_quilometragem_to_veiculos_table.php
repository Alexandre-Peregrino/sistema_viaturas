<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adiciona a coluna 'quilometragem' à tabela 'veiculos'.
     */
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Adiciona a coluna 'quilometragem' como um inteiro grande (bigInteger) e torna-a anulável (nullable).
            // Isso significa que ela não será obrigatória no banco de dados e poderá ser nula.
            $table->bigInteger('quilometragem')->nullable()->after('capacidade_tanque');
        });
    }

    /**
     * Reverse the migrations.
     * Remove a coluna 'quilometragem' da tabela 'veiculos' em caso de rollback.
     */
    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Remove a coluna 'quilometragem' se a migration for revertida.
            $table->dropColumn('quilometragem');
        });
    }
};
