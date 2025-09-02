<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Adiciona colunas no próprio veiculos
        Schema::table('veiculos', function (Blueprint $table) {
            // ajuste tamanhos conforme sua realidade
            $table->string('marca', 100)->nullable()->after('placa');
            $table->string('modelo', 150)->nullable()->after('marca');
        });

        // 2) Se ainda existir a coluna FK, tentar copiar dados da tabela antiga
        if (Schema::hasColumn('veiculos', 'marca_modelo_id') && Schema::hasTable('marca_modelos')) {
            // Backfill (PostgreSQL)
            DB::statement("
                UPDATE veiculos v
                SET marca = mm.marca, modelo = mm.modelo
                FROM marca_modelos mm
                WHERE v.marca_modelo_id = mm.id
            ");
        }

        // 3) Remover a FK e a coluna antiga, se existirem
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'marca_modelo_id')) {
                try { $table->dropForeign(['marca_modelo_id']); } catch (\Throwable $e) { /* ignore se não existir */ }
                $table->dropColumn('marca_modelo_id');
            }
        });

        // 4) (Opcional) dropar a tabela antiga, se quiser remover de vez
        if (Schema::hasTable('marca_modelos')) {
            Schema::drop('marca_modelos');
        }
    }

    public function down(): void
    {
        // rollback simples: recria coluna antiga (sem recuperar a tabela)
        Schema::table('veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculos', 'marca_modelo_id')) {
                $table->unsignedBigInteger('marca_modelo_id')->nullable();
            }
            if (Schema::hasColumn('veiculos', 'marca')) $table->dropColumn('marca');
            if (Schema::hasColumn('veiculos', 'modelo')) $table->dropColumn('modelo');
        });
    }
};
