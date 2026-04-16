<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lotacao extends Model
{
    protected $table = 'lotacoes';

    protected $fillable = [
        'veiculo_id',
        'opm_id',
        'municipio_id',
        'data_entrada',
        'data_saida',
        'motivo',
        'observacao',
        'usuario_id',
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'data_saida'   => 'datetime',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function opm(): BelongsTo
    {
        return $this->belongsTo(Opm::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
