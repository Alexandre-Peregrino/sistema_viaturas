<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manutencaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->string('descricao');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->enum('tipo', ['preventiva', 'corretiva']);
            $table->decimal('valor', 10, 2)->nullable();
            $table->string('oficina')->nullable();
            $table->string('status')->default('aberta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencaos');
    }
};
