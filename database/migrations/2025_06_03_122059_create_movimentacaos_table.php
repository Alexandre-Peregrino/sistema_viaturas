<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->foreignId('opm_origem_id')->constrained('opms')->onDelete('cascade');
            $table->foreignId('opm_destino_id')->constrained('opms')->onDelete('cascade');
            $table->date('data_movimentacao');
            $table->text('motivo')->nullable();
            $table->foreignId('usuario_id')->constrained()->onDelete('set null')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacaos');
    }
};
