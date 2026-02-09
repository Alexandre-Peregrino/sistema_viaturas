<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class RotaVeiculosClient
{
    protected string $baseApi;
    protected string $veiculosUrl;          // .../api/veiculos
    protected string $consultarUrl;         // .../api/consultar_veiculo
    protected string $consultarLocalUrl;    // .../api/consultar_veiculo_local

    protected bool $verifyTls;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retries;
    protected int $retrySleepMs;
    protected ?string $bearer;
    protected ?string $pinIp;

    // flags lidos do .env
    protected bool $asForm;
    protected bool $allowGet;

    // NOVO: em local, permitir rodar sem token
    protected bool $allowMissingBearerInLocal;

    public function __construct()
    {
        $raw = rtrim((string) env('ROTA_VEICULOS_BASE_URL', ''), '/');
        if ($raw === '') {
            throw new \RuntimeException('Defina ROTA_VEICULOS_BASE_URL no .env');
        }

        if (preg_match('~/veiculos$~', $raw)) {
            $this->veiculosUrl = $raw;
            $this->baseApi     = preg_replace('~/veiculos$~', '', $raw);
        } else {
            $this->baseApi     = $raw;
            $this->veiculosUrl = $this->baseApi . '/veiculos';
        }

        $this->consultarUrl      = $this->baseApi . '/consultar_veiculo';
        $this->consultarLocalUrl = $this->baseApi . '/consultar_veiculo_local';

        $this->timeout        = (int) env('ROTA_VEICULOS_TIMEOUT', 60);
        $this->connectTimeout = (int) env('ROTA_CONNECT_TIMEOUT', 20);
        $this->retries        = (int) env('ROTA_RETRIES', 1);
        $this->retrySleepMs   = (int) env('ROTA_RETRY_SLEEP_MS', 500);
        $this->verifyTls      = filter_var(env('ROTA_VEICULOS_TLS_VERIFY', true), FILTER_VALIDATE_BOOL);
        $this->bearer         = trim((string) env('ROTA_BEARER', ''));
        $this->pinIp          = trim((string) env('ROTA_PIN_IP', ''));

        $this->asForm   = filter_var(env('ROTA_VEICULOS_AS_FORM', false), FILTER_VALIDATE_BOOL);
        $this->allowGet = filter_var(env('ROTA_VEICULOS_ALLOW_GET', false), FILTER_VALIDATE_BOOL);

        // NOVO: por padrão true em local (você pode controlar via .env)
        $this->allowMissingBearerInLocal = filter_var(
            env('ROTA_ALLOW_MISSING_BEARER_IN_LOCAL', true),
            FILTER_VALIDATE_BOOL
        );
    }

    protected function http(): PendingRequest
    {
        $isLocal = app()->environment('local');

        // Em produção/homolog: exige token.
        // Em local: permite sem token se a flag estiver true.
        if ($this->bearer === '' && !($isLocal && $this->allowMissingBearerInLocal)) {
            throw new \RuntimeException('ROTA_BEARER ausente no .env (rode php artisan config:clear)');
        }

        $curl = [
            CURLOPT_IPRESOLVE             => CURL_IPRESOLVE_V4,
            CURLOPT_TCP_KEEPALIVE         => 1,
            CURLOPT_TCP_KEEPIDLE          => 10,
            CURLOPT_TCP_KEEPINTVL         => 10,
            CURLOPT_FORBID_REUSE          => true,
            CURLOPT_FRESH_CONNECT         => true,
            CURLOPT_NOSIGNAL              => 1,
            CURLOPT_EXPECT_100_TIMEOUT_MS => 0,
        ];

        if ($this->pinIp !== '') {
            $host = parse_url($this->veiculosUrl, PHP_URL_HOST);
            if ($host) {
                $curl[CURLOPT_RESOLVE] = ["{$host}:443:{$this->pinIp}"];
            }
        }

        $req = Http::timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->retry($this->retries, $this->retrySleepMs)
            ->acceptJson()
            ->when(!$this->asForm, fn ($r) => $r->asJson(), fn ($r) => $r->asForm())
            ->withHeaders([
                'Accept'           => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Expect'           => '',
                'Connection'       => 'close',
                'User-Agent'       => 'Laravel-HTTP-Client',
            ])
            ->withOptions([
                'verify'           => $this->verifyTls,
                'force_ip_resolve' => 'v4',
                'curl'             => $curl,
            ]);

        // NOVO: só adiciona Authorization se existir token
        if ($this->bearer !== '') {
            $req = $req->withToken($this->bearer, 'Bearer');
        }

        return $req;
    }

    /** POST/GET /api/consultar_veiculo { parametro } -> { data:{...} } */
    public function consultarVeiculo(string $parametro): array
    {
        $parametro = strtoupper(preg_replace('/[^A-Z0-9]/', '', $parametro));

        $resp = $this->allowGet
            ? $this->http()->get($this->consultarUrl, ['parametro' => $parametro])
            : $this->http()->post($this->consultarUrl, ['parametro' => $parametro]);

        if ($resp->status() === 404) {
            return [];
        }

        $resp->throw();
        $json = $resp->json();
        $data = Arr::get($json, 'data', []);
        return is_array($data) ? $data : [];
    }

    /** POST/GET /api/consultar_veiculo_local { parametro } -> { data:{..., servicos:[...] } } */
    public function consultarVeiculoComLocais(string $parametro): array
    {
        $parametro = strtoupper(preg_replace('/[^A-Z0-9]/', '', $parametro));

        $resp = $this->allowGet
            ? $this->http()->get($this->consultarLocalUrl, ['parametro' => $parametro])
            : $this->http()->post($this->consultarLocalUrl, ['parametro' => $parametro]);

        if ($resp->status() === 404) {
            return [];
        }

        $resp->throw();
        $json = $resp->json();
        $data = Arr::get($json, 'data', []);
        return is_array($data) ? $data : [];
    }

    /** POST/GET /api/veiculos { orgao_id, parametro? } -> ['items'=>[...]] */
    public function listarVeiculos(array $payload = []): array
    {
        $payload['orgao_id'] = $payload['orgao_id'] ?? (int) env('ROTA_ORGAO_ID', 1);

        $resp = $this->allowGet
            ? $this->http()->get($this->veiculosUrl, $payload)   // query string
            : $this->http()->post($this->veiculosUrl, $payload); // body

        $resp->throw();

        $json  = $resp->json();
        $items = is_array($json) && array_is_list($json) ? $json : ($json['data'] ?? []);
        if (!is_array($items)) $items = [];

        // normaliza chaves
        $items = array_map(fn ($r) => is_array($r) ? array_change_key_case($r, CASE_LOWER) : $r, $items);

        return ['items' => $items, 'meta' => []];
    }

    /** Preferência: rápido por placa, com fallback pra lista */
    public function consultarPorPlacaRobusto(string $placa): array
    {
        $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', $placa));

        try {
            $data = $this->consultarVeiculo($placa);

            // NOVO: só aceita se a API devolveu a mesma placa solicitada
            $placaRetornada = strtoupper((string) ($data['placa'] ?? ''));
            if ($data && $placaRetornada === $placa) {
                return ['items' => [array_change_key_case($data, CASE_LOWER)]];
            }
        } catch (RequestException $e) {
            if ($e->response?->status() !== 404) {
                throw $e;
            }
        }

        $resp  = $this->listarVeiculos(['parametro' => $placa]);
        $items = array_values(array_filter($resp['items'] ?? [], fn ($it) =>
            strtoupper((string)($it['placa'] ?? '')) === $placa
        ));

        return ['items' => $items];
    }


    /** Mapper pro schema local */
    public function transformarParaSchemaLocal(array $r): array
    {
        $r = array_change_key_case($r, CASE_LOWER);

        $placa  = strtoupper(trim((string)($r['placa'] ?? '')));
        $marca  = trim((string)($r['marca'] ?? ''));
        $modelo = trim((string)($r['modelo'] ?? ''));

        return [
            'placa'         => $placa,
            'prefixo'       => trim((string)($r['prefixo'] ?? '')) ?: null,
            'marca'         => $marca ?: null,
            'modelo'        => $modelo ?: null,
            'marca_modelo'  => trim($marca . ' ' . $modelo) ?: null,
            'origem'        => $r['origem'] ?? null,
            'combustivel'   => $r['combustivel'] ?? null,
            'observacao'    => $r['observacao'] ?? null,
        ];
    }
}
