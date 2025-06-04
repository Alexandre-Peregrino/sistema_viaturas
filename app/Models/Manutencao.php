<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manutencao extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'descricao',
        'data_inicio',
        'data_fim',
        'tipo',
        'valor',
        'oficina',
        'status',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
