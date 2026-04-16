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
            // Adiciona a coluna 'status' como string e a define como 'nullable'.
            // Se você sempre espera um valor para 'status', pode remover ->nullable().
            $table->string('status', 255)->nullable()->after('observacao');
        });
    }

    /**
     * Reverse the migrations.
     * Remove a coluna 'status' da tabela 'veiculos' em caso de rollback.
     */
    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
