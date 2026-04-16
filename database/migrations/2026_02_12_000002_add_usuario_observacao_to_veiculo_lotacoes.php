<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function constraintExists(string $constraintName): bool
    {
        $row = DB::selectOne("
            SELECT 1
            FROM pg_constraint
            WHERE conname = ?
            LIMIT 1
        ", [$constraintName]);

        return (bool) $row;
    }

    public function up(): void
    {
        // 1) colunas (idempotente)
        Schema::table('veiculo_lotacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculo_lotacoes', 'observacao')) {
                $table->text('observacao')->nullable()->after('motivo');
            }

            if (!Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('observacao');
            }
        });

        // 2) FK (idempotente por pg_constraint)
        if (Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
            $fkName = 'veiculo_lotacoes_usuario_id_fk';

            if (!$this->constraintExists($fkName)) {
                DB::statement("
                    ALTER TABLE veiculo_lotacoes
                    ADD CONSTRAINT {$fkName}
                    FOREIGN KEY (usuario_id)
                    REFERENCES usuarios (id)
                    ON DELETE SET NULL
                ");
            }
        }

        // 3) índices (idempotente no Postgres)
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_usuario_id_idx ON veiculo_lotacoes (usuario_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_veiculo_id_data_saida_index ON veiculo_lotacoes (veiculo_id, data_saida)");
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_opm_id_data_saida_index ON veiculo_lotacoes (opm_id, data_saida)");
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_municipio_id_data_saida_index ON veiculo_lotacoes (municipio_id, data_saida)");
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_opm_idx ON veiculo_lotacoes (opm_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS veiculo_lotacoes_veiculo_open_idx ON veiculo_lotacoes (veiculo_id) WHERE data_saida IS NULL");
    }

    public function down(): void
    {
        // down seguro: índices
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_veiculo_open_idx");
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_opm_idx");
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_municipio_id_data_saida_index");
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_opm_id_data_saida_index");
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_veiculo_id_data_saida_index");
        DB::statement("DROP INDEX IF EXISTS veiculo_lotacoes_usuario_id_idx");

        // FK
        $fkName = 'veiculo_lotacoes_usuario_id_fk';
        if ($this->constraintExists($fkName)) {
            DB::statement("ALTER TABLE veiculo_lotacoes DROP CONSTRAINT {$fkName}");
        }

        // colunas (idempotente)
        Schema::table('veiculo_lotacoes', function (Blueprint $table) {
            if (Schema::hasColumn('veiculo_lotacoes', 'usuario_id')) {
                $table->dropColumn('usuario_id');
            }
            if (Schema::hasColumn('veiculo_lotacoes', 'observacao')) {
                $table->dropColumn('observacao');
            }
        });
    }
};
