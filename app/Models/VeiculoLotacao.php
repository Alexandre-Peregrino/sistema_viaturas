<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VeiculoLotacao extends Model
{
    use HasFactory;

    protected $table = 'veiculo_lotacoes';

    protected $fillable = [
        'veiculo_id',
        'opm_id',
        'municipio_id',
        'data_entrada',
        'data_saida',
        'motivo',
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'data_saida'   => 'date',
    ];

    /* --------- Relações --------- */

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function opm()
    {
        return $this->belongsTo(Opm::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /* --------- Acessors/Helpers --------- */

    public function getAbertaAttribute(): bool
    {
        return is_null($this->data_saida);
    }

    public function fechar(?Carbon $quando = null): void
    {
        $this->update(['data_saida' => ($quando ?? now())->toDateString()]);
    }

    public static function abrir(array $attrs): self
    {
        // espera: veiculo_id, opm_id, municipio_id?, data_entrada?, motivo?
        $attrs['data_entrada'] = $attrs['data_entrada'] ?? now()->toDateString();
        return static::create($attrs);
    }

    /* --------- Scopes úteis --------- */

    public function scopeAbertas($q)
    {
        return $q->whereNull('data_saida');
    }

    public function scopeDaOpm($q, int $opmId)
    {
        return $q->where('opm_id', $opmId);
    }

    public function scopeDoMunicipio($q, int $municipioId)
    {
        return $q->where('municipio_id', $municipioId);
    }

    /**
     * Interseção com um período [inicio, fim].
     * Aceita string/date/Carbon. Se $fim for null, usa hoje.
     */
    public function scopeIntersectaPeriodo($q, $inicio, $fim = null)
    {
        $ini = $inicio instanceof Carbon ? $inicio : Carbon::parse($inicio);
        $end = $fim ? ($fim instanceof Carbon ? $fim : Carbon::parse($fim)) : now();

        return $q->where(function ($w) use ($ini, $end) {
            $w->whereNull('data_saida')->where('data_entrada', '<=', $end)
              ->orWhere(function ($w2) use ($ini, $end) {
                  $w2->whereNotNull('data_saida')
                     ->where('data_entrada', '<=', $end)
                     ->where('data_saida', '>=', $ini);
              });
        });
    }
}
