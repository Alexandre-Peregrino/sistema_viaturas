<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        'prefixo',
        'placa',
        'marca_modelo',
        'tipo_veiculo',
        'opm_id',
        'ano_fabricacao',
        'chassi',
        'renavam',
        'combustivel',
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
        'dt_final_garantia',
        'garantia_bateria_meses',


        // planilha / cadastro
        'proprietario',
        'contrato',

        // ✅ NOVO: Nº Processo SEI
        'processo_sei',

        'classe_igpn',
        'tipo_igpn',

        'marca',
        'modelo',
        'municipio_id',

        // se esses campos existirem na tabela e você usa na blade:
        'n_serie_bateria',
        'dt_inicial_garantia',
    ];

    protected $casts = [
        'aquisicao_dados'      => 'date',
        'entrega_dados_opm'    => 'date',
        'ativo'                => 'boolean',
        'em_processo_descarga' => 'boolean',
        'dt_inicial_garantia'  => 'date',
        'dt_final_garantia'   => 'date',
        'garantia_bateria_meses' => 'integer',

    ];

    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class);
    }

    public function radio()
    {
        return $this->belongsTo(Radio::class, 'numero_serie_radio', 'numero_serie');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function lotacoes()
    {
        return $this->hasMany(VeiculoLotacao::class);
    }

    public function scopeAtivos($q)
    {
        return $q->where('ativo', true);
    }

    public function scopePorOpm($q, ?int $opmId)
    {
        if (!empty($opmId)) {
            $q->where('opm_id', $opmId);
        }
        return $q;
    }
}
