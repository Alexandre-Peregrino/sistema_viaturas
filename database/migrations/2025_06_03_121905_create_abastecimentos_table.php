<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abastecimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->date('data');
            $table->decimal('litros', 8, 2);
            $table->decimal('valor_litro', 8, 2);
            $table->decimal('valor_total', 10, 2);
            $table->string('posto');
            $table->integer('odometro');
            $table->foreignId('usuario_id')->constrained()->onDelete('set null')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abastecimentos');
    }
};
