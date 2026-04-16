<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('regioes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');                 // ex.: "CPR III", "8º BPM"
            $table->string('tipo');                 // ex.: CPR, BPM, CIPM, CIA, PEL, DPM, CPC, CPM
            $table->foreignId('regiao_pai_id')->nullable()->constrained('regioes')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo','nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regioes');
    }
};
