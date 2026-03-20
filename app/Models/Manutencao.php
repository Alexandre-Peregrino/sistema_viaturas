<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manutencao extends Model
{
    use HasFactory;

    /**
     * Nome da tabela.
     * OBS: o plural "manutencaos" é o padrão que o Laravel gera para "Manutencao".
     * Se você renomear a tabela para "manutencoes" no futuro, ajuste aqui.
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
     */
    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim'    => 'datetime',
        'valor'       => 'decimal:2',
    ];

    /**
     * Constantes de domínio.
     */
    public const STATUSES = ['aberta', 'concluida', 'pendente'];
    public const TYPES    = ['preventiva', 'corretiva'];

    /**
     * Relações
     */
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    /**
     * Mutators: sempre salva em minúsculo no banco (consistência).
     */
    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = is_string($value) ? mb_strtolower(trim($value)) : $value;
    }

    public function setTipoAttribute($value): void
    {
        $this->attributes['tipo'] = is_string($value) ? mb_strtolower(trim($value)) : $value;
    }

    /**
     * Labels para exibição (não afetam o valor "real" do atributo).
     */
    public function getStatusLabelAttribute(): ?string
    {
        $raw = $this->getRawOriginal('status');
        return $raw !== null ? ucfirst($raw) : null;
    }

    public function getTipoLabelAttribute(): ?string
    {
        $raw = $this->getRawOriginal('tipo');
        return $raw !== null ? ucfirst($raw) : null;
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
        return $q->where('status', mb_strtolower(trim($status)));
    }

    public function scopeTipo($q, ?string $tipo)
    {
        if (!$tipo) return $q;
        return $q->where('tipo', mb_strtolower(trim($tipo)));
    }

    /**
     * Helpers de leitura (sempre pelo valor cru do banco).
     */
    public function isAberta(): bool
    {
        return mb_strtolower((string) $this->getRawOriginal('status')) === 'aberta';
    }

    public function isConcluida(): bool
    {
        return mb_strtolower((string) $this->getRawOriginal('status')) === 'concluida';
    }

    public function isPendente(): bool
    {
        return mb_strtolower((string) $this->getRawOriginal('status')) === 'pendente';
    }

    /**
     * Hooks (opcional)
     */
    protected static function booted()
    {
        static::creating(function (Manutencao $m) {
            // normalizações, logs, etc.
        });

        static::updating(function (Manutencao $m) {
            // normalizações, logs, etc.
        });
    }
}
