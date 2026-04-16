<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');                 // ex.: "Mossoró"
            $table->char('uf', 2)->default('RN');   // fixo por enquanto
            $table->string('codigo_ibge')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
            $table->unique(['nome','uf']);         // evita duplicados por grafia igual
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
