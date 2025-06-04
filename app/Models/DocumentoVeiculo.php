<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoVeiculo extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'tipo',
        'numero',
        'validade',
        'arquivo',
        'observacao',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
