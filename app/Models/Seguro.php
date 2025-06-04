<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguro extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'tipo',
        'apolice',
        'seguradora',
        'inicio_vigencia',
        'fim_vigencia',
        'valor',
        'arquivo',
        'observacao',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
