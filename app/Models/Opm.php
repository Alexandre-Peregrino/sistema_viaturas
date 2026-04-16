<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opm extends Model
{
    use HasFactory;

    protected $table = 'opms';

    /**
     * Campos compatíveis com:
     * - seu CRUD (Admin/OpmController)
     * - importações (quando precisar)
     */
    protected $fillable = [
        'id',            // opcional (se algum dia fizer upsert com id externo)
        'sigla',
        'nome',
        'cpr',
        'area',
        'cidade',
        'municipio_id',
        'regiao_id',
        'parent_opm_id',
    ];

    /* -------------------- Relacionamentos -------------------- */

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function usuarios()
    {
        // Considerando que seu model é App\Models\Usuario
        return $this->hasMany(Usuario::class);
    }

    public function municipios()
    {
        return $this->belongsToMany(\App\Models\Municipio::class, 'opm_municipios', 'opm_id', 'municipio_id');
    }

    public function parent()
    {
        return $this->belongsTo(Opm::class, 'parent_opm_id');
    }

    public function children()
    {
        return $this->hasMany(Opm::class, 'parent_opm_id');
    }

    /* ----------------------- Scopes úteis ----------------------- */

    public function scopeCpr($query, ?string $cpr)
    {
        if (!empty($cpr)) {
            $query->where('cpr', $cpr);
        }
        return $query;
    }

    /* ---------------------- Helpers ---------------------- */

    public function getDisplayNameAttribute(): string
    {
        $sigla = trim((string) $this->sigla);
        $nome  = trim((string) ($this->nome ?? ''));
        return $nome !== '' ? "{$sigla} — {$nome}" : $sigla;
    }
}
