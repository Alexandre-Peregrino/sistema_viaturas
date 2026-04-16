<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // habilita extensão necessária (idempotente)
        DB::statement("CREATE EXTENSION IF NOT EXISTS btree_gist");

        // exclusão de sobreposição de intervalos por veículo
        DB::statement("
            ALTER TABLE veiculo_lotacoes
            ADD CONSTRAINT exl_veiculo_periodo_sem_sobreposicao
            EXCLUDE USING gist (
                veiculo_id WITH =,
                daterange(data_entrada, COALESCE(data_saida, 'infinity'::date), '[]') WITH &&
            )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE veiculo_lotacoes DROP CONSTRAINT IF EXISTS exl_veiculo_periodo_sem_sobreposicao");
    }
};
