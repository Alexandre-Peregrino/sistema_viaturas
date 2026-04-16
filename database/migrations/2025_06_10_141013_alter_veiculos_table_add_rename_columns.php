<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Adicione esta linha para usar DB::table()

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Renomear 'cidade_municipio' para 'cidade'
            if (Schema::hasColumn('veiculos', 'cidade_municipio')) {
                $table->renameColumn('cidade_municipio', 'cidade');
            }

            // Remover 'ativo_processo_descarga' e adicionar 'ativo' e 'em_processo_descarga'
            if (Schema::hasColumn('veiculos', 'ativo_processo_descarga')) {
                $table->dropColumn('ativo_processo_descarga');
            }
            $table->boolean('ativo')->default(true);
            $table->boolean('em_processo_descarga')->default(false);
        });

        // Opcional: Se houvesse veículos com dados na coluna 'ativo_processo_descarga'
        // e você quisesse transferir esse estado para 'ativo', você faria isso aqui.
        // Mas como não há veículos inseridos ainda, este passo não é estritamente necessário.
        // DB::table('veiculos')->update(['ativo' => true, 'em_processo_descarga' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Reverter as alterações na ordem inversa para garantir a reversão
            if (Schema::hasColumn('veiculos', 'ativo')) {
                $table->dropColumn('ativo');
            }
            if (Schema::hasColumn('veiculos', 'em_processo_descarga')) {
                $table->dropColumn('em_processo_descarga');
            }
            // Adicionar de volta a coluna original (se for para uma reversão completa)
            $table->boolean('ativo_processo_descarga')->default(false); // Ajuste o default se necessário

            if (Schema::hasColumn('veiculos', 'cidade')) {
                $table->renameColumn('cidade', 'cidade_municipio');
            }
        });
    }
};