<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('veiculos', function (Blueprint $table) {
        $table->id();
        $table->string('marca_modelo');
        $table->year('ano_fabricacao');
        $table->string('situacao_carga');
        $table->foreignId('opm_id')->constrained('opms')->onDelete('cascade');
        $table->string('cidade_municipio');
        $table->string('emprego');
        $table->string('tipo_uso');
        $table->string('layout');
        $table->string('tipo_veiculo');
        $table->string('tracao');
        $table->string('combustivel');
        $table->boolean('ativo_processo_descarga');
        $table->string('placa')->unique();
        $table->string('area');
        $table->string('prefixo');
        $table->string('chassi')->unique();
        $table->string('renavam')->unique();
        $table->string('categoria');
        $table->date('aquisicao_dados')->nullable();
        $table->date('entrega_dados_opm')->nullable();
        $table->string('numero_serie_radio')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
