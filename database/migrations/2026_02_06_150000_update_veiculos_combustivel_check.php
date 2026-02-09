<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove a constraint antiga (se existir)
        DB::statement("ALTER TABLE public.veiculos DROP CONSTRAINT IF EXISTS veiculos_combustivel_check");

        // Recria com lista ampliada (inclui versões com e sem acento para evitar erro futuro)
        DB::statement("
            ALTER TABLE public.veiculos
            ADD CONSTRAINT veiculos_combustivel_check
            CHECK (
                combustivel IS NULL
                OR (combustivel)::text = ANY (
                    (ARRAY[
                        'Gasolina'::character varying,
                        'Diesel'::character varying,
                        'Flex'::character varying,
                        'Eletrico'::character varying,
                        'Elétrico'::character varying,
                        'Hibrido'::character varying,
                        'Híbrido'::character varying,
                        'Alcool'::character varying,
                        'Álcool'::character varying,
                        'Etanol'::character varying,
                        'GNV'::character varying
                    ])::text[]
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE public.veiculos DROP CONSTRAINT IF EXISTS veiculos_combustivel_check");

        // Volta para o padrão antigo
        DB::statement("
            ALTER TABLE public.veiculos
            ADD CONSTRAINT veiculos_combustivel_check
            CHECK (
                combustivel IS NULL
                OR (combustivel)::text = ANY (
                    (ARRAY[
                        'Gasolina'::character varying,
                        'Diesel'::character varying,
                        'Flex'::character varying,
                        'Eletrico'::character varying,
                        'Hibrido'::character varying
                    ])::text[]
                )
            )
        ");
    }
};
