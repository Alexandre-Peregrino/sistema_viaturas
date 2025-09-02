<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\RotawebClient;
use Throwable;

class SyncRotawebUnidades extends Command
{
    protected $signature   = 'rotaweb:sync-unidades {--dry-run : Não grava no banco, só exibe o que faria}';
    protected $description = 'Sincroniza as unidades (OPMs) do RotaWeb para a tabela local opms';

    public function handle(RotawebClient $rota): int
    {
        $this->info('Consultando /api/unidades no RotaWeb...');

        try {
            $resp = $rota->unidades();
        } catch (Throwable $e) {
            $this->error('Falha ao consultar RotaWeb: ' . $e->getMessage());
            return self::FAILURE;
        }

        // O endpoint pode vir como ["data" => [...]] ou diretamente como [...]
        $lista = $resp['data'] ?? $resp ?? [];

        if (!is_array($lista) || empty($lista)) {
            $this->warn('Nenhuma unidade retornada.');
            return self::SUCCESS;
        }

        // Monta linhas para upsert
        $agora = now();
        $rows  = [];
        foreach ($lista as $u) {
            // Esperado: id, nome, sigla
            $id    = (int)($u['id'] ?? 0);
            $nome  = (string)($u['nome'] ?? '');
            $sigla = (string)($u['sigla'] ?? '');

            if ($id <= 0 || $nome === '') {
                $this->warn('Pulando item inválido: ' . json_encode($u));
                continue;
            }

            $rows[] = [
                'id'         => $id,            // mantém o id remoto como id local
                'sigla'      => $sigla,
                'nome'       => $nome,
                // Campos exigidos pelo seu schema:
                'cpr'        => '',             // sem info no RotaWeb
                'area'       => '',             // sem info no RotaWeb (era NOT NULL no seu DB)
                'cidade'     => '',             // sem info no RotaWeb
                'updated_at' => $agora,
                'created_at' => $agora,
            ];
        }

        if (empty($rows)) {
            $this->warn('Nada para importar.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line('--- DRY RUN ---');
            $this->line('Importaria/atualizaria ' . count($rows) . ' unidades.');
            $this->line('Exemplo do primeiro registro: ' . json_encode($rows[0], JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        // Faz upsert por id, atualizando campos principais
        DB::table('opms')->upsert(
            $rows,
            ['id'], // uniqueBy
            ['sigla','nome','cpr','area','cidade','updated_at'] // update cols
        );

        $this->info('Sincronização concluída. Total processado: ' . count($rows));
        return self::SUCCESS;
    }
}
