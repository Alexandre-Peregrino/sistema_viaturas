<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        // Guzzle com base_uri (garante barra final)
        $this->http = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
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

            Log::debug('SISGP GET', [
                'uri'   => $uri,
                'query' => $query,
                'code'  => $res->getStatusCode(),
            ]);

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

            Log::debug('SISGP POST', [
                'uri'  => $uri,
                'code' => $res->getStatusCode(),
            ]);

            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP POST falhou', ['uri' => $uri, 'payload' => $payload, 'error' => $e->getMessage()]);
            return [];
        }
    }

    // ==========================
    // ENDPOINTS já existentes
    // ==========================
    public function consultarUnidadeGet(array $filtros = []): array
    {
        return $this->get('unidade/consulta', $filtros);
    }

    public function consultarUnidadePost(array $payload = []): array
    {
        return $this->post('unidade/consulta', $payload);
    }

    // ==========================
    // NOVOS: Policiais / E-mail
    // ==========================

    /**
     * Lista policiais do endpoint `policiais`.
     * - Se a API aceitar filtro (?cpf=...), utilize $query = ['cpf' => '...'].
     * - Se não aceitar, retorna todos e usa cache curto para não sobrecarregar.
     */
    public function listarPoliciais(array $query = []): array
    {
        if (!empty($query)) {
            $data = $this->get('policiais', $query);
            return $this->unwrapData($data);
        }

        // Sem filtro: usa cache curto
        return Cache::remember('sisgp.policiais.v2', now()->addMinutes(10), function () {
            $data = $this->get('policiais');
            return $this->unwrapData($data);
        });
    }

    /**
     * Busca e-mail de um CPF:
     * 1) Tenta GET com ?cpf=...
     * 2) Se não vier, carrega lista completa (cache) e filtra localmente.
     */
    public function buscarEmailPorCpf(string $cpf): ?string
    {
        $cpf = preg_replace('/\D+/', '', (string)$cpf);

        // 1) Tentativa com filtro (?cpf=...)
        $filtrado = $this->listarPoliciais(['cpf' => $cpf]);

        // Pode vir objeto único
        if (!empty($filtrado) && !array_is_list($filtrado) && is_array($filtrado)) {
            $email = $this->pickEmailFromRow($filtrado);
            if ($email) return $email;

            $rowCpf = preg_replace('/\D+/', '', (string) (
                data_get($filtrado, 'cpf') ?? data_get($filtrado, 'usuario_cpf') ?? ''
            ));
            if ($rowCpf === $cpf) {
                return $this->pickEmailFromRow($filtrado);
            }
        }

        // Pode vir lista
        if (!empty($filtrado) && array_is_list($filtrado)) {
            $email = $this->pickEmailFromRow($filtrado[0] ?? []);
            if ($email) return $email;
        }

        // 2) Fallback: lista completa + filtro local
        $todos = $this->listarPoliciais();
        foreach ($todos as $row) {
            $rowCpf = preg_replace('/\D+/', '', (string) (
                data_get($row, 'cpf') ?? data_get($row, 'usuario_cpf') ?? ''
            ));
            if ($rowCpf !== $cpf) continue;

            return $this->pickEmailFromRow($row);
        }

        return null;
    }

    /**
     * Tenta extrair e-mail de variações comuns de chaves.
     */
    protected function pickEmailFromRow(array $row): ?string
    {
        $candidates = [
            'email',
            'email_institucional',
            'emailInstitucional',
            'emailCorporativo',
            'emailInstit',
            'contato.email',
            'usuario.email',
            'usuario.email_institucional',
        ];

        foreach ($candidates as $key) {
            $val = data_get($row, $key);
            if (is_string($val)) {
                $val = trim($val);
                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    return Str::lower($val);
                }
            }
        }

        return null;
    }

    /**
     * Desembrulha retornos em {data: [...]}, {items: [...]}, {result: ...}, etc.
     * Aceita também objeto único com "cara" de policial.
     */
    protected function unwrapData(array $json): array
    {
        if (array_is_list($json)) return $json;

        foreach (['data','items','result','resultado'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                return array_is_list($json[$key]) ? $json[$key] : [$json[$key]];
            }
        }

        if (isset($json['cpf']) || isset($json['usuario_cpf'])) {
            return [$json];
        }

        return [];
    }

    // ==========================
    // Utilitários de debug / probe
    // ==========================
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
            'ip_used'           => $this->ip(),
            'cpf_set'           => !empty($this->cpf),
            'token_md5_prefix'  => substr($hash, 0, 8),
            'token_b64_preview' => substr($textoEncode, 0, 6) . '…' . substr($textoEncode, -6),
            'token_decoded'     => ($parts[0] ?? '') . '@' . ($parts[1] ?? '') . '@' . ($cpfMasked ?? ''),
            'tls_verify'        => !filter_var(env('SISGP_TLS_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL),
        ];
    }

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
        $uri = 'unidade/consulta';

        $resp = $usePost
            ? $this->requestAndReport('POST', $uri, ['json' => $filtros])
            : $this->requestAndReport('GET',  $uri, ['query' => $filtros]);

        return ['debug' => $info, 'response' => $resp];
    }

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
