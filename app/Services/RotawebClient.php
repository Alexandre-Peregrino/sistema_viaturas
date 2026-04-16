<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RotawebClient
{
    protected $timeout;
    protected $verifyTls;
    protected $bearerFromEnv;
    protected $pinIp;
    protected $baseUrl;

    public function __construct()
    {
        // Inicializações
        $this->timeout = env('ROTAWEB_TIMEOUT', 30);
        $this->verifyTls = filter_var(env('ROTAWEB_VERIFY_TLS', true), FILTER_VALIDATE_BOOLEAN);
        $this->bearerFromEnv = env('ROTAWEB_BEARER');
        $this->pinIp = env('ROTAWEB_PIN_IP', '177.87.9.1');

        // Base URL
        $host = env('SISGP_URL_PROD') ?: env('SISGP_URL_TESTE') ?: env('ROTA_BASE_URL', '');

        if ($host === '') {
            $this->baseUrl = null;
            Log::info('Rotaweb desabilitado (sem URL no env)');
        } else {
            $this->baseUrl = rtrim($host, '/');
        }
    }

    protected function isEnabled(): bool
    {
        return $this->baseUrl !== null;
    }

    public function bearer(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->bearerFromEnv ?: '';
    }

    public function postJson($url, $data = [])
    {
        if (!$this->isEnabled()) {
            Log::debug('Rotaweb desabilitado, retornando []');
            return [];
        }

        // Apenas desabilitado, não chamar API
        return [];
    }

    public function usuario()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function unidades()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function efetivoPrevisto()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function efetivoExecutado()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function efetivoPrevistoGeral()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function forward()
    {
        if (!$this->isEnabled()) return [];
        return [];
    }

    public function raw()
    {
        return [];
    }

    public function http()
    {
        return [];
    }
}
