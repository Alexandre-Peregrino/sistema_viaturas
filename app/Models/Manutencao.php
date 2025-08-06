<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manutencao extends Model
{
    use HasFactory;

    // Define os campos que são mass assignable
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

    // Define os campos de data que devem ser manipulados como instâncias de Carbon
    // Use casts atualizados para Laravel 12:
    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];


    // Relacionamento de Manutencao com Veiculo
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    // Definindo os status possíveis para uma manutenção
    const STATUSES = ['aberta', 'concluida', 'pendente'];

    // Método para obter o status da manutenção com validação interna
    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    // Definir os tipos de manutenção
    const TYPES = ['preventiva', 'corretiva'];

    // Lógica de eventos de criação e atualização
    protected static function booted()
    {
        static::creating(function ($manutencao) {
            // Lógica antes da criação, se necessário
        });

        static::updating(function ($manutencao) {
            // Lógica antes da atualização, se necessário
        });
    }
}
