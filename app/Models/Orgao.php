<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orgao extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'ldap_ip', 'ldap_name'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_orgao');
    }
}
