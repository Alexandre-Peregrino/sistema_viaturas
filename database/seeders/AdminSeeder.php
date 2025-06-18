<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['cpf' => '00000000000'],
            [
                'nome' => 'Administrador do Sistema',
                'perfil' => 'admin',
                'email' => 'admin@frota.com',
                'password' => Hash::make('123456'), // Hash explícito, ignora mutator
                'opm_id' => 1,
            ]
        );
    }
}