<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // deixe generoso; Postgres lida bem
        DB::statement("ALTER TABLE opms ALTER COLUMN nome  TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE opms ALTER COLUMN sigla TYPE VARCHAR(255)");
    }

    public function down(): void
    {
        // ajuste se quiser voltar (chutes comuns; adapte ao que vocês tinham)
        DB::statement("ALTER TABLE opms ALTER COLUMN nome  TYPE VARCHAR(100)");
        DB::statement("ALTER TABLE opms ALTER COLUMN sigla TYPE VARCHAR(10)");
    }
};
