<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'descricao',
        'data_infracao',
        'valor',
        'pago',
        'data_pagamento',
        'observacao',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
