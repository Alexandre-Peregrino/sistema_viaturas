<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('radios', function (Blueprint $table) {
            // Adiciona a coluna 'marca' após 'numero_serie', permitindo valores nulos inicialmente
            $table->string('marca')->nullable()->after('numero_serie');
            // Adiciona a coluna 'observacao' após 'status', permitindo valores nulos
            $table->text('observacao')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radios', function (Blueprint $table) {
            // Em caso de rollback da migração, remove as colunas adicionadas
            $table->dropColumn(['marca', 'observacao']);
        });
    }
};
