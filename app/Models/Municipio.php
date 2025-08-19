<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = ['nome','uf','codigo_ibge','lat','lng'];

    public function opms()
    {
        return $this->hasMany(Opm::class);
    }
}
