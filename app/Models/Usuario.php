<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'cpf',
        'nome',
        'nome_guerra',   // ← novo
        'matricula',     // ← novo
        'titulo',        // ← novo
        'perfil',
        'email',
        'password',
        'permitido',     // ← novo
        'opm_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'permitido' => 'boolean', // ← garante true/false certinho
    ];

    // ---- Mutators / Accessors ----

    // Mantém seu auto-hash
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] =
            (is_string($value) && strlen($value) === 60 && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    // (Opcional) garante CPF só com dígitos
    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D+/', '', (string) $value);
    }

    // Usa CPF como identificador de autenticação
    public function getAuthIdentifierName()
    {
        return 'cpf';
    }

    // ---- Relacionamentos ----
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    // ---- Helpers de perfil ----
    public function isAdmin()
    {
        return strtolower($this->perfil) === 'admin';
    }

    public function isP4()
    {
        return strtolower($this->perfil) === 'p4';
    }
}
