<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->foreignId('municipio_id')->nullable()->after('cidade')->constrained('municipios')->nullOnDelete();
            $table->index('cidade'); // ajuda no backfill e relatórios legados
        });

        // BACKFILL: cria municipios a partir de opms.cidade e vincula
        $cidades = DB::table('opms')
            ->select('cidade')
            ->whereNotNull('cidade')
            ->where('cidade','<>','')
            ->distinct()->pluck('cidade');

        foreach ($cidades as $nome) {
            $mid = DB::table('municipios')->where(['nome'=>$nome,'uf'=>'RN'])->value('id');
            if (!$mid) {
                $mid = DB::table('municipios')->insertGetId([
                    'nome' => $nome,
                    'uf' => 'RN',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('opms')->where('cidade', $nome)->update(['municipio_id' => $mid]);
        }
    }

    public function down(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropIndex(['cidade']);
        });
    }
};
