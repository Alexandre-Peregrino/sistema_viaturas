<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lotacoes')) {
            Schema::create('lotacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
                $table->foreignId('opm_id')->constrained('opms');
                $table->foreignId('municipio_id')->nullable()->constrained('municipios');

                $table->timestamp('data_entrada')->useCurrent();
                $table->timestamp('data_saida')->nullable();

                $table->string('motivo')->nullable();
                $table->text('observacao')->nullable();

                $table->foreignId('usuario_id')->nullable()->constrained('usuarios');

                $table->timestamps();

                $table->index(['veiculo_id', 'data_saida']);
                $table->index(['opm_id', 'data_saida']);
            });

            return;
        }

        Schema::table('lotacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('lotacoes', 'municipio_id')) {
                $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            }
            if (!Schema::hasColumn('lotacoes', 'data_entrada')) {
                $table->timestamp('data_entrada')->useCurrent();
            }
            if (!Schema::hasColumn('lotacoes', 'data_saida')) {
                $table->timestamp('data_saida')->nullable();
            }
            if (!Schema::hasColumn('lotacoes', 'motivo')) {
                $table->string('motivo')->nullable();
            }
            if (!Schema::hasColumn('lotacoes', 'observacao')) {
                $table->text('observacao')->nullable();
            }
            if (!Schema::hasColumn('lotacoes', 'usuario_id')) {
                $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            }
        });

        // índices (criar só se ainda não existirem é chato no Laravel puro;
        // se já tiver, o Postgres pode reclamar. Se der conflito, me diga o nome do índice atual
        Schema::table('lotacoes', function (Blueprint $table) {
            // tente criar; se já existir, você remove estas linhas ou cria com nome custom
            $table->index(['veiculo_id', 'data_saida']);
            $table->index(['opm_id', 'data_saida']);
        });
    }

    public function down(): void
    {
        // normalmente eu não derrubo tabela histórica em produção
        // mas para manter reversível:
        if (Schema::hasTable('lotacoes')) {
            Schema::drop('lotacoes');
        }
    }
};
