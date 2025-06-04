<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('apolice');
            $table->string('seguradora');
            $table->date('inicio_vigencia');
            $table->date('fim_vigencia');
            $table->decimal('valor', 10, 2)->nullable();
            $table->string('arquivo')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguros');
    }
};
