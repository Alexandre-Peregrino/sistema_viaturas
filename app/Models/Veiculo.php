<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

        'proprietario',
        'contrato',
        'processo_sei',

        'classe_igpn',
        'tipo_igpn',

        'marca',
        'modelo',
        'municipio_id',

        'n_serie_bateria',
        'dt_inicial_garantia',
    ];

    protected $casts = [
        'aquisicao_dados'         => 'date',
        'entrega_dados_opm'       => 'date',
        'ativo'                   => 'boolean',
        'em_processo_descarga'    => 'boolean',
        'dt_inicial_garantia'     => 'date',
        'dt_final_garantia'       => 'date',
        'garantia_bateria_meses'  => 'integer',
    ];

    /**
     * ⚠️ Legado/espelho: não use para regras de negócio (OPM oficial vem de lotacaoAtual)
     */
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

    /** ✅ Histórico oficial */
    public function lotacoes(): HasMany
    {
        return $this->hasMany(VeiculoLotacao::class, 'veiculo_id');
    }

    /** ✅ Lotação atual (fonte da verdade da OPM atual) */
    public function lotacaoAtual(): HasOne
    {
        return $this->hasOne(VeiculoLotacao::class, 'veiculo_id')
            ->whereNull('data_saida')
            ->latest('data_entrada');
    }

    public function scopeAtivos($q)
    {
        return $q->where('ativo', true);
    }

    /** ✅ Regra nova: filtra pela lotação atual (aberta) */
    public function scopePorOpm($q, ?int $opmId)
    {
        if (!empty($opmId)) {
            $q->whereHas('lotacaoAtual', function ($l) use ($opmId) {
                $l->where('opm_id', $opmId);
            });
        }
        return $q;
    }
}
