<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE public.veiculos DROP CONSTRAINT IF EXISTS veiculos_opm_id_foreign");
        DB::statement("ALTER TABLE public.veiculos DROP CONSTRAINT IF EXISTS veiculos_opm_id_fk");

        DB::statement("
        ALTER TABLE public.veiculos
        ADD CONSTRAINT veiculos_opm_id_fk
        FOREIGN KEY (opm_id) REFERENCES public.opms(id)
        ON DELETE SET NULL
    ");
    }
    public function down(): void
    {
        DB::statement("ALTER TABLE public.veiculos DROP CONSTRAINT IF EXISTS veiculos_opm_id_fk");

        DB::statement("
        ALTER TABLE public.veiculos
        ADD CONSTRAINT veiculos_opm_id_fk
        FOREIGN KEY (opm_id) REFERENCES public.opms(id)
        ON DELETE SET NULL
    ");
    }
};
