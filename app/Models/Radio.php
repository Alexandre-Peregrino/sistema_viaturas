<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_serie',
        'marca',    // Adicionado aqui
        'modelo',
        'status',
        'observacao', // <<-- GARANTA QUE 'observacao' ESTÁ AQUI
        'opm_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Se houver campos que precisam de casting, adicione-os aqui.
        // Ex: 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];

    /**
     * Define a relação com a Viatura, se um rádio estiver associado a uma.
     * Um rádio pode pertencer a uma viatura.
     */
    public function viatura()
    {
        // Assumindo que 'veiculos' tem uma coluna 'numero_serie_radio'
        // que referencia 'numero_serie' da tabela radios.
        return $this->hasOne(Veiculo::class, 'numero_serie_radio', 'numero_serie');
    }
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }
}
