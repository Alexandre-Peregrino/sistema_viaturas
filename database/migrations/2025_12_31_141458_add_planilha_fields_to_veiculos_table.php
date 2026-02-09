<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (!Schema::hasColumn('veiculos', 'proprietario')) {
                $table->string('proprietario')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'contrato')) {
                $table->string('contrato')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'classe_igpn')) {
                $table->string('classe_igpn')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'tipo_igpn')) {
                $table->string('tipo_igpn')->nullable();
            }

            if (!Schema::hasColumn('veiculos', 'observacao')) {
                $table->text('observacao')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            if (Schema::hasColumn('veiculos', 'proprietario')) {
                $table->dropColumn('proprietario');
            }

            if (Schema::hasColumn('veiculos', 'contrato')) {
                $table->dropColumn('contrato');
            }

            if (Schema::hasColumn('veiculos', 'classe_igpn')) {
                $table->dropColumn('classe_igpn');
            }

            if (Schema::hasColumn('veiculos', 'tipo_igpn')) {
                $table->dropColumn('tipo_igpn');
            }

            if (Schema::hasColumn('veiculos', 'observacao')) {
                $table->dropColumn('observacao');
            }
        });
    }
};

