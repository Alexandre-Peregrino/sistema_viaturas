<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->string('nivel_sigilo', 20)->default('publico')->index();
            $table->string('status_geografico', 30)->default('definido')->index();
        });

        // Backfill inicial: 2ª Seção -> sigiloso
        DB::statement("
            UPDATE veiculos v
            SET nivel_sigilo = 'sigiloso'
            FROM opms o
            WHERE o.id = v.opm_id
              AND o.sigla = '2A SECAO'
        ");

        // Backfill inicial: Reserva -> sem local
        DB::statement("
            UPDATE veiculos v
            SET status_geografico = 'reserva_sem_local'
            FROM opms o
            WHERE o.id = v.opm_id
              AND o.sigla = 'RESERVA'
        ");
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn(['nivel_sigilo', 'status_geografico']);
        });
    }
};