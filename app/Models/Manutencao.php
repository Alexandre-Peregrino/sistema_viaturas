<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manutencao extends Model
{
    use HasFactory;

    /**
     * Nome da tabela (caso não seja o plural padrão do Laravel).
     * Se sua tabela já se chama "manutencoes", pode manter por clareza.
     */
    protected $table = 'manutencaos';

    /**
     * Campos liberados para atribuição em massa.
     */
    protected $fillable = [
        'veiculo_id',
        'descricao',
        'data_inicio',
        'data_fim',
        'tipo',
        'valor',
        'oficina',
        'status',
    ];

    /**
     * Conversões de tipo.
     * - datas como Carbon
     * - valor com 2 casas decimais
     */
    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim'    => 'datetime',
        'valor'       => 'decimal:2',
    ];

    /**
     * Relações
     */

    // Manutenção pertence a um veículo (FK: veiculo_id)
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    // (Opcional futuro) Se existir tabela/oficina como model, você pode ligar aqui:
    // public function oficinaRef()
    // {
    //     return $this->belongsTo(Oficina::class, 'oficina_id');
    // }

    /**
     * Constantes de domínio
     */
    public const STATUSES = ['aberta', 'concluida', 'pendente'];
    public const TYPES    = ['preventiva', 'corretiva'];

    /**
     * Acessores/Mutators simples
     * - Armazene o status/tipo em minúsculas; exiba capitalizado.
     */
    public function getStatusAttribute($value): ?string
    {
        return $value !== null ? ucfirst($value) : null;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = is_string($value) ? mb_strtolower($value) : $value;
    }

    public function getTipoAttribute($value): ?string
    {
        return $value !== null ? ucfirst($value) : null;
    }

    public function setTipoAttribute($value): void
    {
        $this->attributes['tipo'] = is_string($value) ? mb_strtolower($value) : $value;
    }

    /**
     * Scopes úteis
     */
    public function scopeDaOpm($q, int $opmId)
    {
        return $q->whereHas('veiculo', fn($qq) => $qq->where('opm_id', $opmId));
    }

    public function scopeStatus($q, ?string $status)
    {
        if (!$status) return $q;
        return $q->where('status', mb_strtolower($status));
    }

    public function scopeTipo($q, ?string $tipo)
    {
        if (!$tipo) return $q;
        return $q->where('tipo', mb_strtolower($tipo));
    }

    /**
     * Helpers de leitura
     */
    public function isAberta(): bool
    {
        return mb_strtolower($this->getRawOriginal('status')) === 'aberta';
    }

    public function isConcluida(): bool
    {
        return mb_strtolower($this->getRawOriginal('status')) === 'concluida';
    }

    public function isPendente(): bool
    {
        return mb_strtolower($this->getRawOriginal('status')) === 'pendente';
    }

    /**
     * Hooks (se precisar lógica antes de criar/atualizar)
     */
    protected static function booted()
    {
        static::creating(function (Manutencao $m) {
            // ex.: normalizar campos, logs, etc.
        });

        static::updating(function (Manutencao $m) {
            // ex.: normalizar campos, logs, etc.
        });
    }
}
