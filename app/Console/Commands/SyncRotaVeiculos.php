<?php

namespace App\Console\Commands;

use App\Services\RotaVeiculosClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

class SyncRotaVeiculos extends Command
{
    protected $signature = 'rota:sync-veiculos';
    protected $description = 'Sincroniza veículos do ROTA (POST /api/veiculos com Bearer).';

    public function handle(RotaVeiculosClient $client): int
    {
        $this->info('Consultando ROTA /api/veiculos (POST)…');

        try {
            // Se a API aceitar filtros no body, coloque aqui (ex.: ['opm' => 1]):
            $payload = [];

            $data  = $client->listarVeiculos($payload);
            $items = $data['items'] ?? [];

            if (!$items) {
                $this->warn('Nenhum veículo retornado.');
                return self::SUCCESS;
            }

            $rows = [];
            foreach ($items as $remoto) {
                $local = $client->transformarParaSchemaLocal($remoto);

                // Precisa ter pelo menos placa para deduplicar com segurança
                if (empty($local['placa'])) {
                    $this->warn('Ignorado item sem placa: ' . json_encode($remoto, JSON_UNESCAPED_UNICODE));
                    continue;
                }

                $rows[] = array_merge($local, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!$rows) {
                $this->warn('Nada para gravar após normalização.');
                return self::SUCCESS;
            }

            // Deduplicação por placa (mantém a primeira ocorrência)
            $rows = collect($rows)->unique('placa')->values()->all();

            // Se preferir manter a ÚLTIMA ocorrência, use:
            // $rows = collect($rows)->reverse()->unique('placa')->values()->all();

            // Upsert em lote
            DB::table('veiculos')->upsert(
                $rows,
                uniqueBy: ['placa'],
                update: array_diff(array_keys($rows[0]), ['placa', 'created_at'])
            );

            $this->info('Sincronização concluída: ' . count($rows) . ' registros processados.');
            return self::SUCCESS;

        } catch (ConnectionException $e) {
            $this->error('Falha de conexão/timeout ao consultar o ROTA: ' . $e->getMessage());
            return self::FAILURE;

        } catch (RequestException $e) {
            $status = $e->response?->status();
            if (in_array($status, [401, 403])) {
                $this->error('Autenticação no ROTA falhou (' . $status . '). Verifique ROTA_BEARER.');
            } else {
                $this->error('Erro HTTP ao consultar o ROTA (HTTP ' . $status . ').');
            }
            return self::FAILURE;

        } catch (\Throwable $e) {
            $this->error('Erro inesperado: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
