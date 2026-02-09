<?php

namespace App\Models;

use App\Models\Opm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'cpf',
        'nome',
        'nome_guerra',
        'matricula',
        'titulo',
        'perfil',
        'email',

        // senha (por enquanto no banco é NOT NULL)
        'password',

        'permitido',
        'opm_id',

        // campos do cadastro
        'posto_graduacao',
        'numero_praca',
        'rg_militar',
        'telefone',
        'cadastro_completo',
        'solicitacao_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'permitido'         => 'boolean',
        'cadastro_completo' => 'boolean',
    ];

    // -------------------------
    // Mutators / Accessors
    // -------------------------

    /**
     * Mantém auto-hash (necessário porque password é NOT NULL hoje).
     * - Se vier um hash bcrypt pronto, preserva.
     * - Caso contrário, gera hash.
     */
    public function setPasswordAttribute($value): void
    {
        $value = (string) $value;

        $this->attributes['password'] =
            (strlen($value) === 60 && preg_match('/^\$2y\$/', $value))
                ? $value
                : Hash::make($value);
    }

    /**
     * Nome sempre com trim.
     */
    public function setNomeAttribute($value): void
    {
        $this->attributes['nome'] = trim((string) $value);
    }

    /**
     * Nome de guerra com trim; se vazio, salva null.
     * (Se quiser sempre maiúsculo, descomente a linha do strtoupper.)
     */
    public function setNomeGuerraAttribute($value): void
    {
        $v = trim((string) $value);
        if ($v === '') {
            $this->attributes['nome_guerra'] = null;
            return;
        }

        // $v = mb_strtoupper($v);
        $this->attributes['nome_guerra'] = $v;
    }

    /**
     * E-mail normalizado (trim + lowercase). Se vazio, salva null.
     */
    public function setEmailAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['email'] = ($v === '') ? null : mb_strtolower($v);
    }

    /**
     * CPF somente dígitos.
     */
    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = preg_replace('/\D+/', '', (string) $value);
    }

    /**
     * Telefone somente dígitos. Se vazio, salva null.
     */
    public function setTelefoneAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['telefone'] = ($digits === '') ? null : $digits;
    }

    public function setPostoGraduacaoAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['posto_graduacao'] = ($v === '') ? null : $v;
    }

    public function setNumeroPracaAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['numero_praca'] = ($v === '') ? null : $v;
    }

    public function setRgMilitarAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['rg_militar'] = ($v === '') ? null : $v;
    }

    public function setMatriculaAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['matricula'] = ($v === '') ? null : $v;
    }

    /**
     * Usa CPF como identificador de autenticação (em vez de id).
     */
    public function getAuthIdentifierName(): string
    {
        return 'cpf';
    }

    // -------------------------
    // Relacionamentos
    // -------------------------

    public function opm(): BelongsTo
    {
        return $this->belongsTo(Opm::class);
    }

    // -------------------------
    // Helpers de perfil
    // -------------------------

    public function isSuperAdmin(): bool
    {
        return strtolower((string) $this->perfil) === 'super_admin';
    }

    /**
     * Admin inclui Super Admin.
     */
    public function isAdmin(): bool
    {
        $p = strtolower((string) $this->perfil);
        return $p === 'admin' || $p === 'super_admin';
    }

    public function isP4(): bool
    {
        return strtolower((string) $this->perfil) === 'p4';
    }

    /**
     * Acesso efetivo ao sistema:
     * - precisa cadastro completo
     * - e precisa ter sido permitido pelo admin
     */
    public function hasAcesso(): bool
    {
        return (bool) $this->cadastro_completo && (bool) $this->permitido;
    }

    /**
     * Validação “real” de cadastro completo.
     */
    public function isCadastroCompleto(): bool
    {
        $required = [
            $this->cpf,
            $this->nome,
            $this->posto_graduacao,
            $this->numero_praca,
            $this->rg_militar,
            $this->matricula,
            $this->telefone,
            $this->opm_id,
        ];

        foreach ($required as $v) {
            if ($v === null) return false;
            if (is_string($v) && trim($v) === '') return false;
        }

        return true;
    }
}
