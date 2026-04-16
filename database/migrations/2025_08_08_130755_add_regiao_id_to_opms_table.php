<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->foreignId('regiao_id')->nullable()->after('municipio_id')->constrained('regioes')->nullOnDelete();
            $table->index('cpr');  // legado, ajuda em consultas antigas
        });

        // BACKFILL: cria nós de CPR a partir de opms.cpr (texto) e vincula
        $cprs = DB::table('opms')
            ->select('cpr')
            ->whereNotNull('cpr')
            ->where('cpr','<>','')
            ->distinct()->pluck('cpr');

        foreach ($cprs as $nomeCpr) {
            // normalização simples
            $norm = trim(preg_replace('/\s+/', ' ', strtoupper($nomeCpr)));
            $rid = DB::table('regioes')->where(['tipo'=>'CPR','nome'=>$norm])->value('id');
            if (!$rid) {
                $rid = DB::table('regioes')->insertGetId([
                    'nome' => $norm,
                    'tipo' => 'CPR',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('opms')->where('cpr',$nomeCpr)->update(['regiao_id' => $rid]);
        }
    }

    public function down(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('regiao_id');
            $table->dropIndex(['cpr']);
        });
    }
};
