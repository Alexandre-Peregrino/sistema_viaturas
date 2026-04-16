<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('opm_municipios')) return;

        Schema::create('opm_municipios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('opm_id');
            $table->unsignedBigInteger('municipio_id');
            $table->timestamps();

            $table->unique(['opm_id', 'municipio_id']);

            $table->foreign('opm_id')->references('id')->on('opms')->onDelete('cascade');
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');

            $table->index(['opm_id']);
            $table->index(['municipio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opm_municipios');
    }
};
