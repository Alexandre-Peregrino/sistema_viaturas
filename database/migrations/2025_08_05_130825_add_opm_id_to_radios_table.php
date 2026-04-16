<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('radios', function (Blueprint $table) {
            $table->unsignedBigInteger('opm_id')->nullable()->after('status');
            $table->foreign('opm_id')->references('id')->on('opms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('radios', function (Blueprint $table) {
            $table->dropForeign(['opm_id']);
            $table->dropColumn('opm_id');
        });
    }
};
