<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $fillable = [
        
            'marca_modelo_id',
            'tipo_veiculo_id',
            'ano_fabricacao',
            'situacao_carga',
            'opm_id',
            'cidade',
            'emprego',
            'tipo_uso',
            'layout',
            'tracao',
            'combustivel',
            'ativo',
            'em_processo_descarga',
            'placa',
            'area',
            'prefixo',
            'chassi',
            'renavam',
            'categoria',
            'aquisicao_dados',
            'entrega_dados_opm',
            'numero_serie_radio',
    ];
        
    // Relação com OPM
    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    // Relação com Radio
    public function radio()
    {
        return $this->belongsTo(Radio::class, 'numero_serie_radio', 'numero_serie');
    }
    public function tipoVeiculo()
    {
    return $this->belongsTo(TipoVeiculo::class);
    }

    public function marcaModelo()
    {
    return $this->belongsTo(MarcaModelo::class);
    }

}
