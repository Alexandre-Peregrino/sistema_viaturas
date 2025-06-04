<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abastecimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'data',
        'litros',
        'valor_litro',
        'valor_total',
        'posto',
        'odometro',
        'usuario_id',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
