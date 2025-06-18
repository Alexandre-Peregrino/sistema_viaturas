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
        'password',
        'opm_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = strlen($value) === 60 && preg_match('/^\$2y\$/', $value)
            ? $value
            : Hash::make($value);
    }

    public function getAuthIdentifierName()
    {
        return 'cpf';
    }

    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    public function isAdmin()
    {
        return strtolower($this->perfil) === 'admin';
    }

    public function isP4()
    {
        return strtolower($this->perfil) === 'p4';
    }
}