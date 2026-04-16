<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Campos do “completar cadastro”
            $table->string('posto_graduacao', 30)->nullable()->after('titulo');
            $table->string('numero_praca', 20)->nullable()->after('posto_graduacao');
            $table->string('rg_militar', 20)->nullable()->after('numero_praca');
            $table->string('telefone', 20)->nullable()->after('rg_militar');

            // Controle do fluxo
            $table->boolean('cadastro_completo')->default(false)->after('telefone');

            // Status da solicitação (para admin aprovar)
            // none | pending | approved | denied
            $table->string('solicitacao_status', 20)->default('none')->after('cadastro_completo');

            // Índices úteis
            $table->index('cadastro_completo', 'usuarios_cadastro_completo_index');
            $table->index('solicitacao_status', 'usuarios_solicitacao_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex('usuarios_cadastro_completo_index');
            $table->dropIndex('usuarios_solicitacao_status_index');

            $table->dropColumn([
                'posto_graduacao',
                'numero_praca',
                'rg_militar',
                'telefone',
                'cadastro_completo',
                'solicitacao_status',
            ]);
        });
    }
};
