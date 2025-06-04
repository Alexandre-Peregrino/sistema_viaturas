<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opm extends Model
{
    use HasFactory;

    protected $fillable = ['sigla', 'cpr', 'area', 'cidade'];

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}
