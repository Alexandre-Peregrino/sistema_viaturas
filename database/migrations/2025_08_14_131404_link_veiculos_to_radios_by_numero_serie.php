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

    public function up(): void
    {
        // 1) Garante a coluna (se não existir)
        if (!Schema::hasColumn('veiculos', 'numero_serie_radio')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->string('numero_serie_radio')->nullable()->after('categoria');
            });
        }

        // 2) Checagens de dados para evitar falhas no UNIQUE/FK
        $dups = DB::select("
            select numero_serie_radio, count(*) c
            from veiculos
            where numero_serie_radio is not null
            group by numero_serie_radio
            having count(*) > 1
        ");
        if ($dups) {
            $ex = collect($dups)->pluck('numero_serie_radio')->take(5)->implode(', ');
            throw new RuntimeException("Duplicatas em veiculos.numero_serie_radio: {$ex}");
        }

        $invalid = DB::selectOne("
            select v.numero_serie_radio
            from veiculos v
            left join radios r on r.numero_serie = v.numero_serie_radio
            where v.numero_serie_radio is not null
              and r.numero_serie is null
            limit 1
        ");
        if ($invalid) {
            throw new RuntimeException(
                "Há viatura com numero_serie_radio sem correspondente em radios: {$invalid->numero_serie_radio}"
            );
        }

        // 3) UNIQUE + FK (só cria se não existir)
        if (!$this->constraintExists('veiculos_numero_serie_radio_unique')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->unique('numero_serie_radio', 'veiculos_numero_serie_radio_unique');
            });
        }

        if (!$this->constraintExists('veiculos_numero_serie_radio_fk')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->foreign('numero_serie_radio', 'veiculos_numero_serie_radio_fk')
                    ->references('numero_serie')->on('radios')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // Remover FK e UNIQUE se existirem (mantém a coluna)
        if ($this->constraintExists('veiculos_numero_serie_radio_fk')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->dropForeign('veiculos_numero_serie_radio_fk');
            });
        }
        if ($this->constraintExists('veiculos_numero_serie_radio_unique')) {
            Schema::table('veiculos', function (Blueprint $table) {
                $table->dropUnique('veiculos_numero_serie_radio_unique');
            });
        }
    }
};
