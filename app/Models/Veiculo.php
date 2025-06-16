<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos'; // Garantir que a tabela está correta

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefixo',
        'placa',
        'marca_modelo', // CORRIGIDO: Usar 'marca_modelo' conforme o DB
        'tipo_veiculo',
        'opm_id',
        'cor',
        'ano_fabricacao',
        'ano_modelo',
        'chassi',
        'renavam',
        'combustivel',
        'capacidade_tanque',
        'quilometragem',
        'observacao',
        'status',
        'cidade',
        'situacao_carga',
        'emprego',
        'tipo_uso',
        'layout',
        'tracao',
        'area',
        'categoria',
        'aquisicao_dados',
        'entrega_dados_opm',
        'numero_serie_radio',
        'ativo',
        'em_processo_descarga',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aquisicao_dados' => 'date',
        'entrega_dados_opm' => 'date',
        'ativo' => 'boolean',
        'em_processo_descarga' => 'boolean',
    ];

    /**
     * Define a relação com a OPM.
     * Uma viatura pertence a uma OPM.
     */
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    /**
     * Define a relação com as Manutenções.
     * Uma viatura pode ter muitas manutenções.
     */
    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class);
    }

    /**
     * Define a relação com o Rádio.
     */
    public function radio()
    {
        return $this->belongsTo(Radio::class, 'numero_serie_radio', 'numero_serie');
    }
}
