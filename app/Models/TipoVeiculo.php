<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVeiculo extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao'];

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }
}
