<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->string('nome')->nullable(); // novo campo
        });
    }

    public function down(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }
};
