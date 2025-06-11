<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaModelo extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',
        'modelo',
        'categoria',
        'combustivel',
        'tracao',
        'tipo_uso',
        'consumo_medio',
        'ativo',
    ];
    

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    // Acessor para exibir "marca - modelo"
    public function getDescricaoAttribute()
    {
        return "{$this->marca} - {$this->modelo}";
    }
}
