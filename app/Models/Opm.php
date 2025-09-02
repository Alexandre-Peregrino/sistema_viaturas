<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opm extends Model
{
    use HasFactory;

    /**
     * Deixei 'id' nos fillables para permitir upsert com o id externo do RotaWeb.
     * Campos extras (cpr/area/cidade/municipio_id) continuam para uso interno.
     */
    protected $fillable = [
        'id',        // id vindo do RotaWeb (opcional)
        'sigla',
        'nome',      // vem do RotaWeb
        'cpr',       // usamos como “região”
        'area',
        'cidade',
        'municipio_id',
        // 'regiao_id', // se não for usar, pode remover do fillable; deixar aqui é opcional
    ];

    /* -------------------- Relacionamentos -------------------- */

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

   
    /* ----------------------- Scopes úteis ----------------------- */

    /**
     * Filtra por CPR (região).
     */
    public function scopeCpr($query, ?string $cpr)
    {
        if (!empty($cpr)) {
            $query->where('cpr', $cpr);
        }
        return $query;
    }

    /* ---------------------- Helpers opcionais ---------------------- */

    /**
     * Exibe "SIGLA — Nome" para selects e tabelas.
     */
    public function getDisplayNameAttribute(): string
    {
        return trim(($this->sigla ?? '') . ' — ' . ($this->nome ?? ''));
    }
}
