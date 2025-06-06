<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarcaModelosTable extends Migration
{
    public function up(): void
    {
        Schema::create('marca_modelos', function (Blueprint $table) {
            $table->id();
            $table->string('marca'); // Ex: Toyota
            $table->string('modelo'); // Ex: Hilux
            $table->string('categoria')->nullable(); // SUV, caminhonete, etc.
            $table->string('combustivel')->nullable(); // Flex, Diesel, etc.
            $table->string('tracao')->nullable(); // 4x4, 4x2, etc.
            $table->string('tipo_uso')->nullable(); // Urbano, Rural, Misto
            $table->float('consumo_medio')->nullable(); // Ex: 10.5 km/l
            $table->boolean('ativo')->default(true); // Se ainda está em uso
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marca_modelos');
    }
}
