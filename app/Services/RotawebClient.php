<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class RotawebClient
{
    protected bool $enabled;
    protected string $baseUrl;
    protected bool $verifyTls;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retries;
    protected int $retrySleepMs;
    protected ?string $bearerFromEnv;
    protected ?string $pinIp;

    protected string $pathUsuario                 = '/usuario';
    protected string $pathUnidades                = '/unidades';
    protected string $pathEfetivoPrevisto         = '/efetivo/previsto';
    protected string $pathEfetivoExecutado        = '/efetivo/executado';
    protected string $pathEfetivoPrevistoGeral    = '/efetivo/previsto/geral';
    protected string $pathEfetivoExecutadoGeral   = '/efetivo/executado/geral';
    protected string $pathEfetivoPrevistoComVtr   = '/efetivo/previsto/geral/com_vtr';
    protected string $pathEfetivoExecutadoComVtr  = '/efetivo/executado/geral/com_vtr';
    protected string $pathEscalasAgente           = '/escalas/agente';

    public function __construct()
    {
        $this->enabled = env('ROTA_ENABLED', false);
        if (!$this->enabled) return;

        $mode       = strtolower((string) env('ROTA_MODE', 'producao'));
        $baseEnv    = trim((string) env('ROTA_BASE_URL', ''));
        $sisgpProd  = rtrim((string) env('SISGP_URL_PROD', ''), '/');
        $sisgpTest  = rtrim((string) env('SISGP_URL_TESTE', ''), '/');

        if ($baseEnv !== '') {
            $this->baseUrl = rtrim($baseEnv, '/');
        } else {
            $host = $mode === 'teste' ? $sisgpTest : $sisgpProd;
            if ($host === '') {
                throw new \RuntimeException('Defina SISGP_URL_PROD/TESTE ou ROTA_BASE_URL no .env');
            }
            $this->baseUrl = $host . '/api/rotaweb';
        }

        $this->timeout        = (int) env('ROTA_TIMEOUT', 15);
        $this->connectTimeout = (int) env('ROTA_CONNECT_TIMEOUT', 10);
        $this->retries        = (int) env('ROTA_RETRIES', 2);
        $this->retrySleepMs   = (int) env('ROTA_RETRY_SLEEP_MS', 300);
        $this->verifyTls      = filter_var(env('ROTA_VERIFY', true), FILTER_VALIDATE_BOOL);
        $this->bearerFromEnv  = trim((string) env('ROTA_BEARER', ''));
        $this->pinIp          = trim((string) env('ROTA_PIN_IP', ''));
    }

    public function bearer(): string
    {
        if (!$this->enabled) return '';

        if ($this->bearerFromEnv !== '') {
            return $this->bearerFromEnv;
        }

        throw new \RuntimeException('ROTA_BEARER ausente no .env (ou implemente login no método bearer()).');
    }

    protected function raw(): PendingRequest
    {
        if (!$this->enabled) return Http::timeout(0)->acceptJson();

        $req = Http::timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->retry($this->retries, $this->retrySleepMs)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Accept'           => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Connection'       => 'close',
                'Expect'           => '',
                'User-Agent'       => 'Laravel-HTTP-Client',
            ])
            ->withOptions([
                'verify'           => $this->verifyTls,
                'force_ip_resolve' => 'v4',
                'curl' => [
                    CURLOPT_IPRESOLVE             => CURL_IPRESOLVE_V4,
                    CURLOPT_TCP_KEEPALIVE         => 1,
                    CURLOPT_TCP_KEEPIDLE          => 10,
                    CURLOPT_TCP_KEEPINTVL         => 10,
                    CURLOPT_FORBID_REUSE          => true,
                    CURLOPT_FRESH_CONNECT         => true,
                    CURLOPT_NOSIGNAL              => 1,
                    CURLOPT_EXPECT_100_TIMEOUT_MS => 0,
                ],
            ]);

        if ($this->pinIp !== '') {
            $host = parse_url($this->baseUrl, PHP_URL_HOST);
            if ($host) {
                $req = $req->withOptions([
                    'curl' => [
                        CURLOPT_RESOLVE => ["{$host}:443:{$this->pinIp}"],
                    ],
                ]);
            }
        }

        return $req;
    }

    protected function http(): PendingRequest
    {
        if (!$this->enabled) return Http::timeout(0)->acceptJson();

        return $this->raw()->withToken($this->bearer(), 'Bearer');
    }

    protected function postJson(string $path, array $payload = []): array
    {
        if (!$this->enabled) return [];

        $resp = $this->http()->post($this->baseUrl . $path, $payload);

        if ($resp->status() === 404) {
            return [];
        }

        $resp->throw();
        $json = $resp->json();

        if (is_array($json) && array_key_exists('data', $json)) {
            $data = $json['data'];
            return is_array($data) ? $data : [];
        }

        return is_array($json) ? $json : [];
    }

    public function usuario(string $cpf): array
    {
        if (!$this->enabled) return [];

        $cpf = preg_replace('/\D+/', '', $cpf);
        return $this->postJson($this->pathUsuario, ['cpf' => $cpf]);
    }

    public function unidades(array $filtros = []): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathUnidades, $filtros);
    }

    public function efetivoPrevisto(string $operacao, string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoPrevisto, [
            'operacao' => $operacao,
            'inicio'   => $inicio,
            'termino'  => $termino,
        ]);
    }

    public function efetivoExecutado(string $operacao, string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoExecutado, [
            'operacao' => $operacao,
            'inicio'   => $inicio,
            'termino'  => $termino,
        ]);
    }

    public function efetivoPrevistoSemOp(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->efetivoPrevistoGeral($inicio, $termino);
    }

    public function efetivoExecutadoSemOp(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->efetivoExecutadoGeral($inicio, $termino);
    }

    public function efetivoPrevistoGeral(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoPrevistoGeral, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    public function efetivoExecutadoGeral(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoExecutadoGeral, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    public function efetivoPrevistoGeralComVtr(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoPrevistoComVtr, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    public function efetivoExecutadoGeralComVtr(string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        return $this->postJson($this->pathEfetivoExecutadoComVtr, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    public function escalasAgente(string $cpf, string $inicio, string $termino): array
    {
        if (!$this->enabled) return [];

        $cpf = preg_replace('/\D+/', '', $cpf);
        return $this->postJson($this->pathEscalasAgente, [
            'cpf'     => $cpf,
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    public function forward(string $method, string $path, array $payload = []): array
    {
        if (!$this->enabled) return [];

        $method = strtoupper($method);
        $url    = $this->baseUrl . (str_starts_with($path, '/') ? $path : "/{$path}");

        $req = $this->http();

        $resp = match ($method) {
            'GET'  => $req->get($url, $payload),
            'POST' => $req->post($url, $payload),
            'PUT'  => $req->put($url, $payload),
            'PATCH'=> $req->patch($url, $payload),
            'DELETE'=>$req->delete($url, $payload),
            default => throw new \InvalidArgumentException("Método HTTP não suportado: {$method}"),
        };

        if ($resp->status() === 404) {
            return [];
        }

        $resp->throw();
        $json = $resp->json();

        return is_array($json) ? $json : [];
    }
}