<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        // colunas confirmadas via \Schema::getColumnListing('veiculos')
        'prefixo',
        'placa',
        'marca_modelo',          // <- existe NA TABELA (não é *_id)
        'tipo_veiculo',
        'opm_id',
        'ano_fabricacao',
        'chassi',
        'renavam',
        'combustivel',
        'quilometragem',
        'observacao',
        'status',                // existe
        'cidade',                // legado (vamos normalizar com municipios)
        'situacao_carga',
        'emprego',
        'tipo_uso',
        'layout',
        'tracao',
        'area',                  // legado (vamos normalizar com regioes)
        'categoria',
        'aquisicao_dados',
        'entrega_dados_opm',
        'numero_serie_radio',    // legado (futuramente trocar por radio_id)
        'ativo',
        'em_processo_descarga',
    ];

    protected $casts = [
        'aquisicao_dados'        => 'date',
        'entrega_dados_opm'      => 'date',
        'ativo'                  => 'boolean',
        'em_processo_descarga'   => 'boolean',
    ];

    /** Uma viatura pertence a uma OPM. */
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    /** Uma viatura pode ter muitas manutenções. */
    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class);
    }

    /**
     * Relacionamento atual com Rádio usando chave não numérica:
     * veiculos.numero_serie_radio -> radios.numero_serie
     * (compatível com o que existe hoje)
     */
    public function radio()
    {
        return $this->belongsTo(Radio::class, 'numero_serie_radio', 'numero_serie');
    }

    /**
     * Preparado para o histórico de lotações por intervalo (veiculo_lotacoes):
     * assim que você rodar a migration create_veiculo_lotacoes_table,
     * isso já funciona.
     */
    public function lotacoes()
    {
        return $this->hasMany(VeiculoLotacao::class);
    }

    /** Scopes úteis nos relatórios */
    public function scopeAtivos($q)
    {
        return $q->where('ativo', true);
    }

    public function scopeEmOperacao($q)
    {
        // ajuste conforme o padrão de valores do seu "status"
        return $q->where('status', 'operando')->orWhereNull('status');
    }
}
