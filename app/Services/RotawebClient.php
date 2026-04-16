<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class RotawebClient
{
    /** @var string Base da API RotaWeb (ex.: https://www3.pm.rn.gov.br/api/rotaweb) */
    protected string $baseUrl;

    protected bool $verifyTls;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retries;
    protected int $retrySleepMs;
    protected ?string $bearerFromEnv;
    protected ?string $pinIp;

    // ---------- Caminhos (ajuste se necessário) ----------
    protected string $pathUsuario                 = '/usuario';
    protected string $pathUnidades                = '/unidades';

    protected string $pathEfetivoPrevisto         = '/efetivo/previsto';
    protected string $pathEfetivoExecutado        = '/efetivo/executado';

    protected string $pathEfetivoPrevistoGeral    = '/efetivo/previsto/geral';
    protected string $pathEfetivoExecutadoGeral   = '/efetivo/executado/geral';

    protected string $pathEfetivoPrevistoComVtr   = '/efetivo/previsto/geral/com_vtr';
    protected string $pathEfetivoExecutadoComVtr  = '/efetivo/executado/geral/com_vtr';

    protected string $pathEscalasAgente           = '/escalas/agente';
    // ------------------------------------------------------

    public function __construct()
    {
        // Base URL: se houver ROTA_BASE_URL use-a; senão derive do modo + SISGP_URL_*
        $mode       = strtolower((string) env('ROTA_MODE', 'producao'));
        $baseEnv    = trim((string) env('ROTA_BASE_URL', '')); // opcional
        $sisgpProd  = rtrim((string) env('SISGP_URL_PROD', ''), '/');
        $sisgpTest  = rtrim((string) env('SISGP_URL_TESTE', ''), '/');

        if ($baseEnv !== '') {
            $this->baseUrl = rtrim($baseEnv, '/');
        } else {
            // chute sensato: “…/api/rotaweb”; ajuste se seu upstream for diferente
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

        $this->bearerFromEnv  = trim((string) env('ROTA_BEARER', '')); // se a mesma credencial servir
        $this->pinIp          = trim((string) env('ROTA_PIN_IP', '')); // ex.: 177.87.96.77
    }

    /* ==================== AUTH ==================== */

    /**
     * Obtém o token Bearer a usar.
     * Estratégia:
     *  - Se ROTA_BEARER está no .env, usa direto.
     *  - (Opcional) Você pode implementar login via user/senha e cache com TTL — deixei ganchos abaixo.
     */
    public function bearer(): string
    {
        if ($this->bearerFromEnv !== '') {
            return $this->bearerFromEnv;
        }

        // ---------- Exemplo (desativado): login com usuário/senha e cache ----------
        // $cacheKey = 'rotaweb_bearer';
        // return Cache::remember($cacheKey, (int) env('ROTA_TOKEN_TTL', 3300), function () {
        //     $user = trim((string) env('ROTA_USER', ''));
        //     $pass = trim((string) env('ROTA_PASS', ''));
        //     if ($user === '' || $pass === '') {
        //         throw new \RuntimeException('Credenciais ROTA_USER/ROTA_PASS ausentes e ROTA_BEARER não definido.');
        //     }
        //     // Ajuste o endpoint de login caso exista:
        //     $resp = $this->raw()->post($this->baseUrl . '/auth/login', [
        //         'usuario' => $user,
        //         'senha'   => $pass,
        //     ])->throw()->json();
        //     $token = (string) Arr::get($resp, 'token', '');
        //     if ($token === '') {
        //         throw new \RuntimeException('Falha ao obter token do RotaWeb.');
        //     }
        //     return $token;
        // });
        // ---------------------------------------------------------------------------

        // Sem bearer não dá pra prosseguir:
        throw new \RuntimeException('ROTA_BEARER ausente no .env (ou implemente login no método bearer()).');
    }

    /* ==================== HTTP base ==================== */

    /** Configuração “crua”, sem Authorization (usada para login, se necessário) */
    protected function raw(): PendingRequest
    {
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

    /** Configuração autenticada (usa Bearer) */
    protected function http(): PendingRequest
    {
        return $this->raw()->withToken($this->bearer(), 'Bearer');
    }

    /** Helper POST JSON com tratamento padrão */
    protected function postJson(string $path, array $payload = []): array
    {
        $resp = $this->http()->post($this->baseUrl . $path, $payload);

        if ($resp->status() === 404) {
            return [];
        }

        $resp->throw();
        $json = $resp->json();

        // Algumas rotas podem vir como { data: [...] } outras como array direto
        if (is_array($json) && array_key_exists('data', $json)) {
            $data = $json['data'];
            return is_array($data) ? $data : [];
        }

        return is_array($json) ? $json : [];
    }

    /* ==================== MÉTODOS USADOS NO PROJETO ==================== */

    /** GET/POST /usuario  (ajuste p/ POST se o upstream exigir) */
    public function usuario(string $cpf): array
    {
        $cpf = preg_replace('/\D+/', '', $cpf);
        return $this->postJson($this->pathUsuario, ['cpf' => $cpf]);
    }

    /** /unidades */
    public function unidades(array $filtros = []): array
    {
        return $this->postJson($this->pathUnidades, $filtros);
    }

    /** Efetivo previsto por operação */
    public function efetivoPrevisto(string $operacao, string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoPrevisto, [
            'operacao' => $operacao,
            'inicio'   => $inicio,
            'termino'  => $termino,
        ]);
    }

    /** Efetivo executado por operação */
    public function efetivoExecutado(string $operacao, string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoExecutado, [
            'operacao' => $operacao,
            'inicio'   => $inicio,
            'termino'  => $termino,
        ]);
    }

    /** Efetivo previsto sem operação (alias de “geral”) */
    public function efetivoPrevistoSemOp(string $inicio, string $termino): array
    {
        return $this->efetivoPrevistoGeral($inicio, $termino);
    }

    /** Efetivo executado sem operação (alias de “geral”) */
    public function efetivoExecutadoSemOp(string $inicio, string $termino): array
    {
        return $this->efetivoExecutadoGeral($inicio, $termino);
    }

    /** Efetivo previsto geral */
    public function efetivoPrevistoGeral(string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoPrevistoGeral, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    /** Efetivo executado geral */
    public function efetivoExecutadoGeral(string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoExecutadoGeral, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    /** Efetivo previsto geral com viaturas */
    public function efetivoPrevistoGeralComVtr(string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoPrevistoComVtr, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    /** Efetivo executado geral com viaturas */
    public function efetivoExecutadoGeralComVtr(string $inicio, string $termino): array
    {
        return $this->postJson($this->pathEfetivoExecutadoComVtr, [
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    /** Escalas de um agente (por CPF) em um período */
    public function escalasAgente(string $cpf, string $inicio, string $termino): array
    {
        $cpf = preg_replace('/\D+/', '', $cpf);
        return $this->postJson($this->pathEscalasAgente, [
            'cpf'     => $cpf,
            'inicio'  => $inicio,
            'termino' => $termino,
        ]);
    }

    /* ==================== UTILIDADE GENÉRICA (Opcional) ==================== */

    /**
     * Encaminha requisições arbitrárias para o upstream.
     * Útil para o seu RotawebProxyController em novas rotas.
     */
    public function forward(string $method, string $path, array $payload = []): array
    {
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
