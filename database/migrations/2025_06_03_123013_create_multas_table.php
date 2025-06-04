<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->text('descricao');
            $table->date('data_infracao');
            $table->decimal('valor', 10, 2);
            $table->boolean('pago')->default(false);
            $table->date('data_pagamento')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};
