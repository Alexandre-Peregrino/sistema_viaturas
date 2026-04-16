<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private function constraintExists(string $name): bool
    {
        $row = DB::selectOne("select 1 from pg_constraint where conname = ?", [$name]);
        return (bool) $row;
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne("
            select 1
            from pg_indexes
            where tablename = ? and indexname = ?
        ", [$table, $index]);
        return (bool) $row;
    }

    public function up(): void
    {
        // Se houver duplicatas, avisa (evita travar no unique).
        $dups = DB::select("
            select numero_serie, count(*) c
            from radios
            where numero_serie is not null
            group by numero_serie
            having count(*) > 1
        ");
        if ($dups) {
            $ex = collect($dups)->pluck('numero_serie')->take(5)->implode(', ');
            throw new RuntimeException("Existem numeros de serie duplicados em 'radios': {$ex}");
        }

        Schema::table('radios', function (Blueprint $table) {
            // vazio: vamos criar fora do closure conforme checagens
        });

        // UNIQUE(numero_serie)
        if (!$this->constraintExists('radios_numero_serie_unique')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->unique('numero_serie', 'radios_numero_serie_unique');
            });
        }

        // INDEX(status)
        if (!$this->indexExists('radios', 'radios_status_idx')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->index('status', 'radios_status_idx');
            });
        }

        // INDEX(opm_id)
        if (!$this->indexExists('radios', 'radios_opm_id_idx')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->index('opm_id', 'radios_opm_id_idx');
            });
        }
    }

    public function down(): void
    {
        // DROP INDEX/CONSTRAINT somente se existirem
        if ($this->indexExists('radios', 'radios_opm_id_idx')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->dropIndex('radios_opm_id_idx');
            });
        }
        if ($this->indexExists('radios', 'radios_status_idx')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->dropIndex('radios_status_idx');
            });
        }
        if ($this->constraintExists('radios_numero_serie_unique')) {
            Schema::table('radios', function (Blueprint $table) {
                $table->dropUnique('radios_numero_serie_unique');
            });
        }
    }
};
