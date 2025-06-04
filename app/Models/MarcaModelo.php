<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaModelo extends Model
{
    use HasFactory;

    protected $fillable = ['marca', 'modelo'];

    // Se os veículos usam essa tabela
    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }
}

