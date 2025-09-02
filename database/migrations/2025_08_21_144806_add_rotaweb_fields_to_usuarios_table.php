<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Campos trazidos do RotaWeb
            if (!Schema::hasColumn('usuarios', 'nome_guerra')) {
                $table->string('nome_guerra')->nullable()->after('nome');
            }
            if (!Schema::hasColumn('usuarios', 'matricula')) {
                $table->string('matricula', 20)->nullable()->after('cpf');
            }
            if (!Schema::hasColumn('usuarios', 'titulo')) {
                $table->string('titulo', 50)->nullable()->after('nome_guerra');
            }

            // (Opcional) índices úteis – só crie se ainda não existir a coluna
            if (!Schema::hasColumn('usuarios', 'perfil')) {
                $table->string('perfil', 10)->nullable()->index();
            }
            if (!Schema::hasColumn('usuarios', 'permitido')) {
                $table->boolean('permitido')->default(false)->index();
            }
            if (!Schema::hasColumn('usuarios', 'opm_id')) {
                $table->unsignedBigInteger('opm_id')->nullable()->index();
                // Se a tabela opms existir, crie a FK
                if (Schema::hasTable('opms')) {
                    $table->foreign('opm_id')->references('id')->on('opms')->nullOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'titulo')) {
                $table->dropColumn('titulo');
            }
            if (Schema::hasColumn('usuarios', 'nome_guerra')) {
                $table->dropColumn('nome_guerra');
            }
            if (Schema::hasColumn('usuarios', 'matricula')) {
                $table->dropColumn('matricula');
            }
            // Não mexo em perfil/permitido/opm_id no down por segurança
        });
    }
};
