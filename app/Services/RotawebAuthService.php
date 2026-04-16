<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RotawebAuthService
{
    public function __construct(private RotawebClient $client) {}

    /**
     * Autentica no RotaWeb com CPF/senha do usuário.
     * Retorna: [
     *   'token'     => '...',
     *   'usuario'   => [...payload original do /api/usuario...],
     *   'normalized'=> ['cpf'=>..., 'nome'=>..., 'nome_guerra'=>..., 'matricula'=>..., 'titulo'=>...]
     * ]
     */
    public function attempt(string $cpf, string $password): array
    {
        $cpf = preg_replace('/\D+/', '', $cpf ?? '');

        if ($cpf === '' || $password === '') {
            throw new RuntimeException('CPF e senha são obrigatórios.');
        }

        $base = $this->client->baseUrl();
        $http = Http::withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->asJson()
            ->acceptJson()
            ->timeout(config('rotaweb.timeout', 15));

        // Alguns ambientes pedem o CSRF do Sanctum antes do login
        try {
            $http->get($base . '/sanctum/csrf-cookie');
        } catch (\Throwable $e) {
            // ignora
        }

        // 1) login
        $resp = $http->post($base . '/api/login', [
            'cpf'      => $cpf,
            'password' => $password,
        ]);

        $json = $resp->json();

        if ($resp->failed()) {
            $msg = is_array($json)
                ? ($json['msg'] ?? $json['message'] ?? ($json['data']['error'] ?? 'Falha no login'))
                : $resp->body();

            throw new RuntimeException('Falha de login no RotaWeb: ' . $msg);
        }

        // Alguns backends usam {"success":false,"data":{"error":"Não Autorizado"}}
        if (is_array($json) && isset($json['success']) && $json['success'] === false) {
            $msg = $json['data']['error'] ?? $json['message'] ?? 'Não autorizado';
            throw new RuntimeException('Falha de login no RotaWeb: ' . $msg);
        }

        $token = is_array($json) ? ($json['data']['token'] ?? null) : null;
        if (!$token) {
            throw new RuntimeException('RotaWeb não retornou token.');
        }

        // 2) dados do usuário autenticado
        $usuario = $this->usuarioPorToken($token);

        // 3) normalização útil para provisionamento local
        $norm = [
            'cpf'         => $usuario['usuario_cpf']        ?? $cpf,
            'nome'        => $usuario['usuario_nome']       ?? ($usuario['nome'] ?? ''),
            'nome_guerra' => $usuario['usuario_nome_guerra']?? ($usuario['nome_guerra'] ?? ''),
            'matricula'   => $usuario['usuario_matricula']  ?? ($usuario['matricula'] ?? ''),
            'titulo'      => $usuario['usuario_titulo']     ?? ($usuario['titulo'] ?? ''),
        ];

        return ['token' => $token, 'usuario' => $usuario, 'normalized' => $norm];
    }

    /**
     * Apenas realiza o login e retorna o token Bearer.
     */
    public function login(string $cpf, string $password): string
    {
        $cpf = preg_replace('/\D+/', '', $cpf ?? '');
        if ($cpf === '' || $password === '') {
            throw new RuntimeException('CPF e senha são obrigatórios.');
        }

        $base = $this->client->baseUrl();
        $http = Http::withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->asJson()->acceptJson()->timeout(config('rotaweb.timeout', 15));

        try { $http->get($base . '/sanctum/csrf-cookie'); } catch (\Throwable $e) {}

        $resp = $http->post($base . '/api/login', [
            'cpf'      => $cpf,
            'password' => $password,
        ]);

        $json = $resp->json();

        if ($resp->failed()) {
            $msg = is_array($json)
                ? ($json['msg'] ?? $json['message'] ?? ($json['data']['error'] ?? 'Falha no login'))
                : $resp->body();
            throw new RuntimeException('Falha de login no RotaWeb: ' . $msg);
        }

        if (is_array($json) && isset($json['success']) && $json['success'] === false) {
            $msg = $json['data']['error'] ?? $json['message'] ?? 'Não autorizado';
            throw new RuntimeException('Falha de login no RotaWeb: ' . $msg);
        }

        $token = is_array($json) ? ($json['data']['token'] ?? null) : null;
        if (!$token) {
            throw new RuntimeException('RotaWeb não retornou token.');
        }

        return $token;
    }

    /**
     * Consulta /api/usuario usando um token já obtido.
     * Retorna o array de dados (prioriza "data" se existir).
     */
    public function usuarioPorToken(string $token): array
    {
        $base = $this->client->baseUrl();
        $http = Http::withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->asJson()->acceptJson()->timeout(config('rotaweb.timeout', 15));

        $resp = $http->withToken($token)->post($base . '/api/usuario', []);

        $json = $resp->json();

        if ($resp->failed()) {
            $msg = is_array($json) ? ($json['msg'] ?? $json['message'] ?? '') : '';
            throw new RuntimeException('Falha ao consultar /api/usuario no RotaWeb: ' . ($msg ?: $resp->body()));
        }

        // Alguns backends retornam {retorno:"erro", msg:"..."} com 200
        if (is_array($json) && ($json['retorno'] ?? null) === 'erro') {
            $msg = $json['msg'] ?? 'Erro desconhecido';
            throw new RuntimeException('Falha ao consultar /api/usuario no RotaWeb: ' . $msg);
        }

        // Manual indica {"data": {...}}
        return $json['data'] ?? $json ?? [];
    }
}
