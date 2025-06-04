<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'cpf',
        'nome',
        'perfil',
        'email',
        'senha',
        'permitido',
        'opm_id',
    ];

    protected $hidden = [
        'senha',
    ];

    // Para usar autenticação, vamos mapear senha para password
    public function getAuthPassword()
    {
        return $this->senha;
    }

    // Relação com OPM
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    // Função para verificar se é admin
    public function isAdmin()
    {
        return $this->perfil === 'admin';
    }

    // Função para verificar se é P4
    public function isP4()
    {
        return $this->perfil === 'p4';
    }
}
