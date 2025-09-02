<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RotaVeiculosClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class VeiculoSyncController extends Controller
{
    public function __construct()
    {
        // Garante autenticação e permissão de administrador em todas as actions
        $this->middleware(['auth', 'can:isAdmin']);
    }

    /**
     * Consulta rápida no ROTA por PLACA e exibe uma prévia (não grava).
     * Agora retorna TODOS os campos do veículo consultado.
     */
    public function probe(Request $request, RotaVeiculosClient $client)
    {
        $request->validate([
            'placa' => ['required', 'string', 'max:12'],
        ]);

        $placa = strtoupper(trim((string) $request->input('placa')));

        try {
            // Usa a rota de placa direta com fallback para /veiculos
            $resp  = $client->consultarPorPlacaRobusto($placa);
            $items = $resp['items'] ?? [];
        } catch (ConnectionException $e) {
            return back()->withErrors(['rota' => 'Timeout/conexão ao consultar o ROTA.'])->withInput();
        } catch (RequestException $e) {
            $status = $e->response?->status();
            if (in_array($status, [401, 403], true)) {
                return back()->withErrors(['rota' => 'Autenticação no ROTA falhou (' . $status . '). Verifique ROTA_BEARER.'])->withInput();
            }
            return back()->withErrors(['rota' => 'Erro ao consultar o ROTA (HTTP ' . ($status ?? 'desconhecido') . ').'])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['rota' => $e->getMessage()])->withInput();
        }

        if (!$items) {
            return back()->withErrors(['rota' => 'Placa não encontrada no ROTA.'])->withInput();
        }

        // Busca por placa deve retornar só 1; envia o item completo para a view
        $veiculo = $items[0]; // já vem normalizado em lowercase pelo client
        return back()->with('rota_veiculo', $veiculo)->withInput();
    }

    /**
     * (Opcional) Sincroniza veículos do ROTA para a base local (upsert por placa).
     * Se você não for persistir localmente, pode remover esta action e sua rota/botão.
     */
    public function sync(Request $request, RotaVeiculosClient $client)
    {
        $request->validate([
            'updated_since' => ['nullable', 'string'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $perPage = $request->integer('per_page', 200);
        $updated = $request->input('updated_since');
        $total   = 0;

        try {
            // Sem paginação documentada: busca uma “página” e encerra
            $payload = array_filter([
                'per_page'      => $perPage,
                'updated_since' => $updated,
            ]);

            $data  = $client->listarVeiculos($payload);
            $items = $data['items'] ?? [];
            if (!$items) {
                return back()->with('status', 'Sincronização concluída: nenhum item retornado pelo ROTA.');
            }

            $now  = now();
            $rows = [];
            foreach ($items as $remoto) {
                $local = $client->transformarParaSchemaLocal($remoto);
                if (empty($local['placa'])) {
                    // upsert exige chave única para deduplicar
                    continue;
                }
                $rows[] = array_merge($local, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($rows) {
                // Dedup por placa para evitar colisões desnecessárias
                $rows = collect($rows)->unique('placa')->values()->all();

                // Ajuste o nome da tabela se necessário
                DB::table('veiculos')->upsert(
                    $rows,
                    uniqueBy: ['placa'],
                    update: array_values(array_diff(array_keys($rows[0]), ['placa', 'created_at']))
                );

                $total = count($rows);
            }
        } catch (ConnectionException $e) {
            return back()->withErrors(['rota' => 'Timeout/conexão ao sincronizar com o ROTA.']);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            if (in_array($status, [401, 403], true)) {
                return back()->withErrors(['rota' => "Autenticação no ROTA falhou ({$status}). Verifique ROTA_BEARER."]);
            }
            return back()->withErrors(['rota' => 'Erro ao sincronizar com o ROTA (HTTP ' . ($status ?? 'desconhecido') . ').']);
        } catch (\Throwable $e) {
            return back()->withErrors(['rota' => $e->getMessage()]);
        }

        return back()->with('status', "Sincronização concluída: {$total} veículos processados.");
    }
}
