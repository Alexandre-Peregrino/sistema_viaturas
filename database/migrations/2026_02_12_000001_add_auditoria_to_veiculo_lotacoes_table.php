<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculo_lotacoes', function (Blueprint $table) {
            // ✅ Observação da movimentação
            if (!Schema::hasColumn('veiculo_lotacoes', 'observacao')) {
                $table->text('observacao')->nullable()->after('motivo');
            }

            // ✅ Quem fez a movimentação
            if (!Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('observacao');
            }
        });

        // FK em segundo passo (evita problemas em alguns ambientes)
        Schema::table('veiculo_lotacoes', function (Blueprint $table) {
            if (Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
                // index ajuda consultas por usuário/auditoria
                $table->index('usuario_id', 'veiculo_lotacoes_usuario_id_idx');

                $table->foreign('usuario_id', 'veiculo_lotacoes_usuario_id_fk')
                    ->references('id')
                    ->on('usuarios')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculo_lotacoes', function (Blueprint $table) {
            // drop FK / index se existirem
            if (Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
                $table->dropForeign('veiculo_lotacoes_usuario_id_fk');
                $table->dropIndex('veiculo_lotacoes_usuario_id_idx');
                $table->dropColumn('usuario_id');
            }

            if (Schema::hasColumn('veiculo_lotacoes', 'observacao')) {
                $table->dropColumn('observacao');
            }
        });
    }
};
