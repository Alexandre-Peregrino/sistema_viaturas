<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radio extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_serie',
        'marca',
        'modelo',
        'status',
        'observacao',
        'opm_id',
    ];

    protected $casts = [
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    /**
     * Um rádio pode estar instalado em UMA viatura (ou em nenhuma).
     * foreignKey em veiculos: numero_serie_radio
     * ownerKey em radios:    numero_serie
     */
    public function viatura()
    {
        return $this->hasOne(Veiculo::class, 'numero_serie_radio', 'numero_serie');
    }

    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    /**
     * Escopo: rádios disponíveis = não vinculados a nenhuma viatura.
     * Opcionalmente, passe uma lista de status permitidos:
     *   Radio::disponiveis(['disponivel','estoque'])->get()
     */
    public function scopeDisponiveis($query, ?array $statusPermitidos = null)
    {
        $query->whereDoesntHave('viatura');

        if (!empty($statusPermitidos)) {
            $query->whereIn('status', $statusPermitidos);
        }

        return $query;
    }

    /**
     * (Opcional) normaliza numero_serie: TRIM + UPPERCASE
     */
    public function setNumeroSerieAttribute($value): void
    {
        $this->attributes['numero_serie'] = strtoupper(trim((string) $value));
    }
}
