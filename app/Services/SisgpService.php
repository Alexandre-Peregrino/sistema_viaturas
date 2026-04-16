<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SisgpService
{
    /**
     * Bases e credenciais
     */
    protected string $root;
    protected string $baseUrlV2; // .../sisgpws/api/v2/rotaweb
    protected string $baseUrlV1; // .../sisgpws/api/v1

    protected string $sistema;
    protected string $semente;
    protected string $cpf;

    /**
     * HTTP clients
     */
    protected Client $httpV2;
    protected Client $httpV1;

    public function __construct()
    {
        $config = config('sisgp');

        $this->sistema = (string) ($config['sistema'] ?? 'ROTAWEB');
        $this->semente = (string) ($config['semente'] ?? '');
        $this->cpf     = (string) ($config['user_cpf'] ?? '');

        $this->root = (($config['mode'] ?? 'test') === 'producao')
            ? (string) ($config['urls']['producao'] ?? '')
            : (string) ($config['urls']['test'] ?? '');

        // Bases
        $this->baseUrlV2 = rtrim($this->root, '/') . '/sisgpws/api/v2/rotaweb';
        $this->baseUrlV1 = rtrim($this->root, '/') . '/sisgpws/api/v1';

        $verifyTls = !filter_var(env('SISGP_TLS_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL);

        // Client V2
        $this->httpV2 = new Client([
            'base_uri' => rtrim($this->baseUrlV2, '/') . '/',
            'timeout'  => 30,
            'verify'   => $verifyTls,
        ]);

        // Client V1
        $this->httpV1 = new Client([
            'base_uri' => rtrim($this->baseUrlV1, '/') . '/',
            'timeout'  => 30,
            'verify'   => $verifyTls,
        ]);
    }

    /**
     * IP usado na composição do token.
     * Em muitos ambientes Linux, gethostbyname(gethostname()) vira 127.0.1.1.
     * Se o SISGP exigir IP real de rede, defina SISGP_IP no .env.
     */
    protected function ip(): string
    {
        $hostname = gethostname() ?: 'localhost';
        return (string) env('SISGP_IP', gethostbyname($hostname));
    }

    /**
     * Token = md5(YYYYMMDD + sistema + base64(sistema@ip@cpf) + semente) + '@' + base64(...)
     */
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

    /* ============================================================
     * V2 - GET/POST genérico (rotaweb)
     * ============================================================ */

    protected function getV2(string $uri, array $query = []): array
    {
        try {
            $res = $this->httpV2->get($uri, [
                'headers' => $this->headers(),
                'query'   => $query,
            ]);

            $json = json_decode($res->getBody()->getContents(), true);

            Log::debug('SISGP V2 GET', [
                'uri'   => $uri,
                'query' => $query,
                'code'  => $res->getStatusCode(),
            ]);

            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP V2 GET falhou', [
                'uri'   => $uri,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    protected function postV2(string $uri, array $payload = []): array
    {
        try {
            $res = $this->httpV2->post($uri, [
                'headers' => $this->headers(),
                'json'    => $payload,
            ]);

            $json = json_decode($res->getBody()->getContents(), true);

            Log::debug('SISGP V2 POST', [
                'uri'  => $uri,
                'code' => $res->getStatusCode(),
            ]);

            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP V2 POST falhou', [
                'uri'     => $uri,
                'payload' => $payload,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    /* ============================================================
     * V1 - GET genérico (para endpoints específicos)
     * ============================================================ */

    protected function getV1(string $uri, array $query = []): array
    {
        try {
            $res = $this->httpV1->get($uri, [
                'headers' => $this->headers(),
                'query'   => $query,
            ]);

            $json = json_decode($res->getBody()->getContents(), true);

            Log::debug('SISGP V1 GET', [
                'uri'   => $uri,
                'query' => $query,
                'code'  => $res->getStatusCode(),
            ]);

            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::warning('SISGP V1 GET falhou', [
                'uri'   => $uri,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /* ============================================================
     * Endpoints V2 existentes
     * ============================================================ */

    public function consultarUnidadeGet(array $filtros = []): array
    {
        return $this->getV2('unidade/consulta', $filtros);
    }

    public function consultarUnidadePost(array $payload = []): array
    {
        return $this->postV2('unidade/consulta', $payload);
    }

    /* ============================================================
     * NOVO (V1): Buscar policial por CPF ou Matrícula
     * Endpoint: /sisgpws/api/v1/policiais/matricula-cpf/{st_parametro}
     * Retorna objeto "policial" (não lista)
     * ============================================================ */

    public function buscarPolicialPorCpfOuMatricula(string $stParametro): ?array
    {
        $stParametro = trim((string) $stParametro);
        if ($stParametro === '') {
            return null;
        }

        // Cache curto (evita bater no SISGP a cada navegação)
        $cacheKey = 'sisgp.v1.policiais.matricula_cpf.' . md5($stParametro);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($stParametro) {
            $json = $this->getV1('policiais/matricula-cpf/' . rawurlencode($stParametro));

            // Alguns endpoints podem retornar {} quando não encontra
            if (empty($json) || !is_array($json)) {
                return null;
            }

            // Se vier com "data"/"result", desempacota
            if (isset($json['data']) && is_array($json['data'])) {
                $json = $json['data'];
            } elseif (isset($json['result']) && is_array($json['result'])) {
                $json = $json['result'];
            } elseif (isset($json['resultado']) && is_array($json['resultado'])) {
                $json = $json['resultado'];
            }

            // Se ainda assim estiver vazio, null
            return !empty($json) ? $json : null;
        });
    }

    /**
     * Normaliza um "policial" do SISGP para uso interno.
     * Como não temos o payload real aqui, mantemos conservador e tentamos múltiplas chaves.
     */
    public function normalizarPolicial(array $row): array
    {
        $cpf = preg_replace('/\D+/', '', (string) (
            data_get($row, 'cpf') ??
            data_get($row, 'usuario_cpf') ??
            data_get($row, 'usuario.cpf') ??
            ''
        ));

        $nome = (string) (
            data_get($row, 'nome') ??
            data_get($row, 'nome_completo') ??
            data_get($row, 'usuario.nome') ??
            data_get($row, 'pessoa.nome') ??
            data_get($row, 'pessoa.nomeCompleto') ??
            ''
        );

        $matricula = (string) (
            data_get($row, 'matricula') ??
            data_get($row, 'matricula_funcional') ??
            data_get($row, 'usuario.matricula') ??
            data_get($row, 'pessoa.matricula') ??
            ''
        );

        $email = (string) (
            $this->pickEmailFromRow($row) ??
            ''
        );

        $un = $this->pickUnidadeFromRow($row);

        return [
            'cpf'       => $cpf,
            'nome'      => trim($nome),
            'matricula' => trim($matricula),
            'email'     => trim($email),
            // Unidade/Lotação (conservador)
            'unidade_sigla'      => (string) ($un['sigla'] ?? ''),
            'unidade_nome'       => (string) ($un['nome'] ?? ''),
            'unidade_id_externa' => (string) ($un['id_externa'] ?? ''),
            // Dump útil (opcional) para debug
            'raw' => $row,
        ];
    }

    /**
     * Tenta inferir unidade/lotação/OPM por chaves comuns.
     * Sem payload real do SISGP, é tentativa conservadora.
     */
    protected function pickUnidadeFromRow(array $row): array
    {
        $candidates = [
            // estruturas comuns
            ['base' => 'unidade'],
            ['base' => 'opm'],
            ['base' => 'lotacao'],
            ['base' => 'lotacao_atual'],
            ['base' => 'unidade_atual'],
            ['base' => 'unidadeLotacao'],
        ];

        foreach ($candidates as $c) {
            $base = $c['base'];

            $sigla = data_get($row, "{$base}.sigla") ?? data_get($row, "{$base}.sg_unidade") ?? null;
            $nome  = data_get($row, "{$base}.nome")  ?? data_get($row, "{$base}.nm_unidade") ?? null;
            $idExt = data_get($row, "{$base}.id")    ?? data_get($row, "{$base}.ce_unidade") ?? data_get($row, "{$base}.codigo") ?? null;

            if ($sigla || $nome || $idExt) {
                return [
                    'sigla'      => is_string($sigla) ? trim($sigla) : '',
                    'nome'       => is_string($nome) ? trim($nome) : '',
                    'id_externa' => is_scalar($idExt) ? (string) $idExt : '',
                ];
            }
        }

        // fallback bem conservador: procurar chaves planas
        $sigla = data_get($row, 'sigla') ?? data_get($row, 'sigla_unidade') ?? null;
        $nome  = data_get($row, 'unidade') ?? data_get($row, 'nome_unidade') ?? null;

        return [
            'sigla'      => is_string($sigla) ? trim($sigla) : '',
            'nome'       => is_string($nome) ? trim($nome) : '',
            'id_externa' => '',
        ];
    }

    /* ============================================================
     * Policiais V2 / E-mail (mantido)
     * ============================================================ */

    public function listarPoliciais(array $query = []): array
    {
        if (!empty($query)) {
            $data = $this->getV2('policiais', $query);
            return $this->unwrapData($data);
        }

        return Cache::remember('sisgp.policiais.v2', now()->addMinutes(10), function () {
            $data = $this->getV2('policiais');
            return $this->unwrapData($data);
        });
    }

    public function buscarEmailPorCpf(string $cpf): ?string
    {
        $cpf = preg_replace('/\D+/', '', (string) $cpf);

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

        // 2) Fallback: lista completa + filtro local (EVITE no login; mantenho por compatibilidade)
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

    protected function unwrapData(array $json): array
    {
        if (array_is_list($json)) return $json;

        foreach (['data', 'items', 'result', 'resultado'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                return array_is_list($json[$key]) ? $json[$key] : [$json[$key]];
            }
        }

        if (isset($json['cpf']) || isset($json['usuario_cpf'])) {
            return [$json];
        }

        return [];
    }

    /* ============================================================
     * Debug / Probe (mantido) + Probe V1
     * ============================================================ */

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
            'root'              => $this->root,
            'base_url_v2'        => $this->baseUrlV2,
            'base_url_v1'        => $this->baseUrlV1,
            'ip_used'           => $this->ip(),
            'cpf_set'           => !empty($this->cpf),
            'token_md5_prefix'  => substr($hash, 0, 8),
            'token_b64_preview' => substr($textoEncode, 0, 6) . '…' . substr($textoEncode, -6),
            'token_decoded'     => ($parts[0] ?? '') . '@' . ($parts[1] ?? '') . '@' . ($cpfMasked ?? ''),
            'tls_verify'        => !filter_var(env('SISGP_TLS_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL),
        ];
    }

    protected function requestAndReportV2(string $method, string $uri, array $opts = []): array
    {
        try {
            $start = microtime(true);
            $opts['headers'] = array_merge($opts['headers'] ?? [], $this->headers());
            $opts['http_errors'] = false;

            $res = $this->httpV2->request($method, $uri, $opts);

            $elapsed = (int) round((microtime(true) - $start) * 1000);
            $body = (string) $res->getBody();
            $fullUrl = (string) $this->httpV2->getConfig('base_uri') . ltrim($uri, '/');

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
            $fullUrl = (string) $this->httpV2->getConfig('base_uri') . ltrim($uri, '/');
            return [
                'ok'     => false,
                'method' => $method,
                'url'    => $fullUrl,
                'error'  => $e->getMessage(),
                'class'  => get_class($e),
            ];
        }
    }

    protected function requestAndReportV1(string $method, string $uri, array $opts = []): array
    {
        try {
            $start = microtime(true);
            $opts['headers'] = array_merge($opts['headers'] ?? [], $this->headers());
            $opts['http_errors'] = false;

            $res = $this->httpV1->request($method, $uri, $opts);

            $elapsed = (int) round((microtime(true) - $start) * 1000);
            $body = (string) $res->getBody();
            $fullUrl = (string) $this->httpV1->getConfig('base_uri') . ltrim($uri, '/');

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
            $fullUrl = (string) $this->httpV1->getConfig('base_uri') . ltrim($uri, '/');
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
            ? $this->requestAndReportV2('POST', $uri, ['json' => $filtros])
            : $this->requestAndReportV2('GET',  $uri, ['query' => $filtros]);

        return ['debug' => $info, 'response' => $resp];
    }

    /**
     * Probe padrão: V2 (rotaweb)
     */
    public function probe(string $method, string $path, array $params = [], bool $asJson = false): array
    {
        $opts = $asJson ? ['json' => $params] : ['query' => $params];
        return $this->requestAndReportV2(strtoupper($method), ltrim($path, '/'), $opts);
    }

    public function probeJson(string $path, array $json): array
    {
        return $this->requestAndReportV2('POST', ltrim($path, '/'), ['json' => $json]);
    }

    public function probeForm(string $path, array $form): array
    {
        return $this->requestAndReportV2('POST', ltrim($path, '/'), ['form_params' => $form]);
    }

    /**
     * Probe V1: para testar endpoints fora do /v2/rotaweb
     */
    public function probeV1(string $method, string $path, array $params = [], bool $asJson = false): array
    {
        $opts = $asJson ? ['json' => $params] : ['query' => $params];
        return $this->requestAndReportV1(strtoupper($method), ltrim($path, '/'), $opts);
    }
}
