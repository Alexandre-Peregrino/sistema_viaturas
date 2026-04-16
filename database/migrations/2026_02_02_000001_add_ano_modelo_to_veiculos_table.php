<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            // Evita quebrar se já existir
            if (!Schema::hasColumn('veiculos', 'ano_modelo')) {
                // year() funciona bem no Postgres (vira smallint)
                $table->year('ano_modelo')->nullable()->after('ano_fabricacao');
                $table->index('ano_modelo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'ano_modelo')) {
                // dropIndex precisa do nome real do índice em alguns bancos;
                // no Postgres/Laravel geralmente resolve com dropIndex(['ano_modelo'])
                try { $table->dropIndex(['ano_modelo']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('ano_modelo');
            }
        });
    }
};
