<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) derruba o CHECK antigo (se existir)
        DB::statement("ALTER TABLE veiculos DROP CONSTRAINT IF EXISTS veiculos_tipo_check");

        // 2) cria a coluna para 'Outros'
        Schema::table('veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculos', 'tipo_veiculo_outro')) {
                $table->string('tipo_veiculo_outro', 255)->nullable();
            }
        });

        // 3) cria CHECK novo com a lista + 'Outros'
        // Observação: usamos btrim para garantir que não aceite string vazia em 'Outros'
        DB::statement("
            ALTER TABLE veiculos
            ADD CONSTRAINT veiculos_tipo_check
            CHECK (
                tipo_veiculo IS NULL
                OR tipo_veiculo::text = ANY (
                    ARRAY[
                        'SUV',
                        'Moto',
                        'Sedan',
                        'Hatch',
                        'Van',
                        'Caminhonete',
                        'Camioneta',
                        'Ônibus',
                        'Micro-Ônibus',
                        'Caminhão',
                        'Utilitário',
                        'Reboque',
                        'Outros'
                    ]::text[]
                )
            )
        ");

        // 4) CHECK garantindo coerência do campo "Outros"
        DB::statement("
            ALTER TABLE veiculos
            ADD CONSTRAINT veiculos_tipo_outro_check
            CHECK (
                (tipo_veiculo IS NULL AND tipo_veiculo_outro IS NULL)
                OR (tipo_veiculo <> 'Outros' AND tipo_veiculo_outro IS NULL)
                OR (tipo_veiculo = 'Outros' AND tipo_veiculo_outro IS NOT NULL AND btrim(tipo_veiculo_outro) <> '')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE veiculos DROP CONSTRAINT IF EXISTS veiculos_tipo_outro_check");
        DB::statement("ALTER TABLE veiculos DROP CONSTRAINT IF EXISTS veiculos_tipo_check");

        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'tipo_veiculo_outro')) {
                $table->dropColumn('tipo_veiculo_outro');
            }
        });

        // Se quiser, você poderia recriar o CHECK antigo aqui.
        // Eu não recomendo voltar para a lista curta.
    }
};
