<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SisgpService
{
    protected string $baseUrl;
    protected string $sistema;
    protected string $semente;
    protected string $cpf;
    protected Client $http;

    public function __construct()
    {
        $config = config('sisgp');

        $this->sistema = $config['sistema'] ?? 'ROTAWEB';
        $this->semente = $config['semente'] ?? '';
        $this->cpf     = $config['user_cpf'] ?? '';

        $root = ($config['mode'] ?? 'test') === 'producao'
            ? ($config['urls']['producao'] ?? '')
            : ($config['urls']['test'] ?? '');

        // base do RotaWeb (sem endpoint final)
        $this->baseUrl = rtrim($root, '/') . '/sisgpws/api/v2/rotaweb';

        // 1) Garante barra no final da base_uri
        $this->http = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',  // ← importante
            'timeout'  => 30,
            'verify'   => !filter_var(env('SISGP_TLS_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    protected function ip(): string
    {
        $hostname = gethostname() ?: 'localhost';
        return env('SISGP_IP', gethostbyname($hostname));
    }

    protected function makeToken(): string
    {
        $ip = $this->ip();
        $textoEncode  = base64_encode("{$this->sistema}@{$ip}@{$this->cpf}");
        $data         = now()->format('Ymd');
        $textoCifrado = md5($data . $this->sistema . $textoEncode . $this->semente);
        return "{$textoCifrado}@{$textoEncode}";
    }

    protected function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Token'  => $this->makeToken(),
        ];
    }

    /** GET genérico */
    protected function get(string $uri, array $query = []): array
    {
        try {
            $res = $this->http->get($uri, [
                'headers' => $this->headers(),
                'query'   => $query,
            ]);
            $json = json_decode($res->getBody()->getContents(), true);
            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP GET falhou', ['uri' => $uri, 'query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /** POST genérico */
    protected function post(string $uri, array $payload = []): array
    {
        try {
            $res = $this->http->post($uri, [
                'headers' => $this->headers(),
                'json'    => $payload,
            ]);
            $json = json_decode($res->getBody()->getContents(), true);
            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP POST falhou', ['uri' => $uri, 'payload' => $payload, 'error' => $e->getMessage()]);
            return [];
        }
    }

    // 2) NUNCA usar barra inicial aqui
    public function consultarUnidadeGet(array $filtros = []): array
    {
        return $this->get('unidade/consulta', $filtros);
    }

    public function consultarUnidadePost(array $payload = []): array
    {
        return $this->post('unidade/consulta', $payload);
    }

    // ===== utilitários de debug =====
    public function debugInfo(): array
    {
        $ip = $this->ip();
        $textoEncode = base64_encode("{$this->sistema}@{$ip}@{$this->cpf}");
        $data = now()->format('Ymd');
        $hash = md5($data . $this->sistema . $textoEncode . $this->semente);

        $decoded = base64_decode($textoEncode) ?: '';
        $parts = explode('@', $decoded);
        $cpfMasked = isset($parts[2]) ? substr($parts[2], 0, 3) . '******' . substr($parts[2], -2) : null;

        return [
            'mode'              => config('sisgp.mode'),
            'base_url'          => $this->baseUrl,
            'ip_used'           => $ip,
            'cpf_set'           => !empty($this->cpf),
            'token_md5_prefix'  => substr($hash, 0, 8),
            'token_b64_preview' => substr($textoEncode, 0, 6) . '…' . substr($textoEncode, -6),
            'token_decoded'     => ($parts[0] ?? '') . '@' . ($parts[1] ?? '') . '@' . ($cpfMasked ?? ''),
            'tls_verify'        => !filter_var(env('SISGP_TLS_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL),
        ];
    }

    // 3) (opcional) mostrar URL efetiva no retorno
    protected function requestAndReport(string $method, string $uri, array $opts = []): array
    {
        try {
            $start = microtime(true);
            $opts['headers'] = array_merge($opts['headers'] ?? [], $this->headers());
            $opts['http_errors'] = false;

            $res = $this->http->request($method, $uri, $opts);

            $elapsed = (int) round((microtime(true) - $start) * 1000);
            $body = (string) $res->getBody();
            $fullUrl = (string) $this->http->getConfig('base_uri') . ltrim($uri, '/');

            return [
                'ok'           => $res->getStatusCode() >= 200 && $res->getStatusCode() < 300,
                'method'       => $method,
                'url'          => $fullUrl,
                'status'       => $res->getStatusCode(),
                'time_ms'      => $elapsed,
                'content_type' => $res->getHeaderLine('content-type'),
                'resp_headers' => array_map(fn($v) => implode('; ', $v), $res->getHeaders()), 
                'body_len'     => strlen($body),
                'body_sample'  => mb_substr($body, 0, 1500),
            ];
        } catch (\Throwable $e) {
            $fullUrl = (string) $this->http->getConfig('base_uri') . ltrim($uri, '/');
            return [
                'ok'     => false,
                'method' => $method,
                'url'    => $fullUrl,
                'error'  => $e->getMessage(),
                'class'  => get_class($e),
            ];
        }
    }



    public function smokeUnidade(bool $usePost = false, array $filtros = []): array
    {
        $info = $this->debugInfo();
        $uri = 'unidade/consulta'; // ← sem barra inicial

        $resp = $usePost
            ? $this->requestAndReport('POST', $uri, ['json' => $filtros])
            : $this->requestAndReport('GET',  $uri, ['query' => $filtros]);

        return ['debug' => $info, 'response' => $resp];
    }
    // dentro da classe SisgpService
    // Já temos o requestAndReport(). Vamos expor probes simples:

    public function probe(string $method, string $path, array $params = [], bool $asJson = false): array
    {
        $opts = $asJson ? ['json' => $params] : ['query' => $params];
        return $this->requestAndReport(strtoupper($method), ltrim($path, '/'), $opts);
    }

    public function probeJson(string $path, array $json): array
    {
        return $this->requestAndReport('POST', ltrim($path, '/'), ['json' => $json]);
    }

    public function probeForm(string $path, array $form): array
    {
        return $this->requestAndReport('POST', ltrim($path, '/'), ['form_params' => $form]);
    }
}
