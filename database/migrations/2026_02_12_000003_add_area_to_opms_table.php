<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            if (!Schema::hasColumn('opms', 'area')) {
                $table->string('area', 50)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('opms', function (Blueprint $table) {
            if (Schema::hasColumn('opms', 'area')) {
                $table->dropColumn('area');
            }
        });
    }
};
