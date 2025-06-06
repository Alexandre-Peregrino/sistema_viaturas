<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaModelo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'fabricante',
        'categoria',
        'tipo',
        'potencia',
        'consumo_medio',
        'durabilidade_estimativa_km',
        'blindado',
        'ativo',
    ];

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }
}
