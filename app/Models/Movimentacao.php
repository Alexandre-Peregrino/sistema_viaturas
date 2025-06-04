<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo_id',
        'opm_origem_id',
        'opm_destino_id',
        'data_movimentacao',
        'motivo',
        'usuario_id',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function opmOrigem()
    {
        return $this->belongsTo(Opm::class, 'opm_origem_id');
    }

    public function opmDestino()
    {
        return $this->belongsTo(Opm::class, 'opm_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
