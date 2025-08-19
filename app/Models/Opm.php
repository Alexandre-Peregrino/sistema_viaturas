<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opm extends Model
{
    use HasFactory;

    protected $fillable = [
        'sigla',
        'cpr',
        'area',
        'cidade',
        'municipio_id', // novo campo (FK para municipios)
        'regiao_id'     // novo campo (FK para regioes)
    ];

    /**
     * Relacionamento com veículos desta OPM
     */
    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    /**
     * Relacionamento com usuários desta OPM
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    /**
     * Relacionamento com município (nova estrutura)
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com região (nova estrutura hierárquica)
     */
    public function regiao()
    {
        return $this->belongsTo(Regiao::class);
    }
}
