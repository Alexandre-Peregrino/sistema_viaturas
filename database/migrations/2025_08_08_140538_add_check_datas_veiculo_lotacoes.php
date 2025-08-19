<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE veiculo_lotacoes
            ADD CONSTRAINT chk_datas_coerentes
            CHECK (data_saida IS NULL OR data_saida >= data_entrada)
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE veiculo_lotacoes DROP CONSTRAINT IF EXISTS chk_datas_coerentes");
    }
};
