<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('numero')->nullable();
            $table->date('validade')->nullable();
            $table->string('arquivo')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_veiculos');
    }
};
