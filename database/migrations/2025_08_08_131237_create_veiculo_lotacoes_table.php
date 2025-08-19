<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('veiculo_lotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('opm_id')->constrained('opms')->cascadeOnDelete();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->nullOnDelete(); // snapshot
            $table->date('data_entrada');
            $table->date('data_saida')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['opm_id','data_saida']);
            $table->index(['municipio_id','data_saida']);
            $table->index(['veiculo_id','data_saida']);
        });

        // SEED INICIAL: cria uma lotação "atual" para cada veículo
        // data_entrada = aquisicao_dados || entrega_dados_opm || created_at (primeira disponível)
        $veiculos = DB::table('veiculos')->select('id','opm_id','aquisicao_dados','entrega_dados_opm','created_at')->get();

        // mapa opm -> municipio_id atual (se existir)
        $opmMunicipio = DB::table('opms')->pluck('municipio_id','id'); // [opm_id => municipio_id]

        foreach ($veiculos as $v) {
            if (!$v->opm_id) continue;

            $entrada = $v->aquisicao_dados ?? $v->entrega_dados_opm ?? \Illuminate\Support\Carbon::parse($v->created_at)->toDateString();
            $muni = $opmMunicipio[$v->opm_id] ?? null;

            // cria apenas se não existir lotação aberta
            $existe = DB::table('veiculo_lotacoes')
                ->where('veiculo_id',$v->id)
                ->whereNull('data_saida')
                ->exists();

            if (!$existe) {
                DB::table('veiculo_lotacoes')->insert([
                    'veiculo_id'   => $v->id,
                    'opm_id'       => $v->opm_id,
                    'municipio_id' => $muni,
                    'data_entrada' => $entrada,
                    'data_saida'   => null,
                    'motivo'       => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculo_lotacoes');
    }
};
