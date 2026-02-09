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
        /**
         * IMPORTANTE:
         * - NÃO coloque auth/can aqui, senão o probe nunca funciona com debug token.
         * - As proteções de acesso devem ser feitas nas ROTAS (web.php), não no construtor.
         */
    }

    /**
     * Consulta rápida no ROTA por PLACA e exibe uma prévia (não grava).
     * - Se request espera JSON -> retorna JSON (ideal para curl)
     * - Caso contrário -> volta para a tela anterior com flash (para uso via browser)
     */
    public function probe(Request $request, RotaVeiculosClient $client)
    {
        $request->validate([
            'placa' => ['required', 'string', 'max:12'],
        ]);

        $placa = strtoupper(trim((string) $request->input('placa')));

        try {
            $resp  = $client->consultarPorPlacaRobusto($placa);
            $items = $resp['items'] ?? [];
        } catch (ConnectionException $e) {
            return $this->probeError($request, 504, 'Timeout/conexão ao consultar o ROTA.');
        } catch (RequestException $e) {
            $status = $e->response?->status();
            if (in_array($status, [401, 403], true)) {
                return $this->probeError(
                    $request,
                    401,
                    'Autenticação no ROTA falhou (' . $status . '). Verifique ROTA_BEARER.'
                );
            }
            return $this->probeError(
                $request,
                502,
                'Erro ao consultar o ROTA (HTTP ' . ($status ?? 'desconhecido') . ').'
            );
        } catch (\Throwable $e) {
            return $this->probeError($request, 500, $e->getMessage());
        }

        if (!$items) {
            return $this->probeError($request, 404, 'Placa não encontrada no ROTA.');
        }

        $veiculo = $items[0]; // normalizado em lowercase pelo client

        // Se for AJAX/JSON (curl com Accept: application/json), devolve JSON
        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'placa'   => $placa,
                'veiculo' => $veiculo,
            ], 200);
        }

        // Caso seja browser, mantém comportamento "volta com flash"
        return back()->with('rota_veiculo', $veiculo)->withInput();
    }

    private function probeError(Request $request, int $status, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => false,
                'message' => $message,
            ], $status);
        }

        return back()->withErrors(['rota' => $message])->withInput();
    }

    /**
     * (Opcional) Sincroniza veículos do ROTA para a base local (upsert por placa).
     * Mantém proteção via rotas (auth/can) no web.php.
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
                    continue;
                }
                $rows[] = array_merge($local, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($rows) {
                $rows = collect($rows)->unique('placa')->values()->all();

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
