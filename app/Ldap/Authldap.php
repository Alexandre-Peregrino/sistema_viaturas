<?php

namespace App\Ldap;

use Illuminate\Support\Facades\Log;

class Authldap
{
    /** Credenciais dinamicamente carregadas do .env */
    protected string $domain;
    protected string $host;
    protected int    $port;
    protected bool   $useTls;
    protected string $baseDn;
    protected ?string $bindDn;
    protected ?string $bindPassword;

    /** Conexão LDAP */
    protected $conn = null;

    public function __construct()
    {
        $this->domain       = env('LDAP_DOMAIN', 'pm.govrn');
        $this->host         = env('LDAP_HOST',   '10.9.192.95');
        $this->port         = (int) env('LDAP_PORT', 389);
        $this->useTls       = filter_var(env('LDAP_USE_TLS', false), FILTER_VALIDATE_BOOL);
        $this->baseDn       = env('LDAP_BASE_DN', 'DC=pm,DC=govrn');

        // Conta técnica (bind) para consultas
        $this->bindDn       = env('LDAP_BIND_DN');        // ex.: svc_ad_reader@pm.govrn
        $this->bindPassword = env('LDAP_BIND_PASSWORD');  // senha
    }

    /* ============================================================
     *  Login “do usuário” (bind simples com cpf@domínio + senha)
     * ============================================================ */
    public function autenticar(array $credenciais): bool
    {
        $cpf   = preg_replace('/\D+/', '', (string)($credenciais['cpf'] ?? ''));
        $senha = (string)($credenciais['password'] ?? '');

        if ($cpf === '' || $senha === '') {
            Log::warning('[LDAP] autenticar(): CPF ou senha vazios.');
            return false;
        }

        $userUpn = "{$cpf}@{$this->domain}";

        try {
            $this->connect();

            if (@ldap_bind($this->conn, $userUpn, $senha)) {
                Log::info('[LDAP] autenticar(): bind OK', ['upn' => $userUpn]);
                return true;
            }

            Log::warning('[LDAP] autenticar(): bind falhou', [
                'upn' => $userUpn,
                'errno' => ldap_errno($this->conn),
                'error' => ldap_error($this->conn),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('[LDAP] autenticar() exception: ' . $e->getMessage());
            return false;
        } finally {
            $this->close();
        }
    }

    /* ============================================================
     *  Busca por CPF usando conta de serviço (bind técnico)
     * ============================================================ */
    public function searchByCpfService(string $cpf): ?array
    {
        $cpf = preg_replace('/\D+/', '', $cpf);

        if ($cpf === '') {
            Log::warning('[LDAP] searchByCpfService(): CPF vazio.');
            return null;
        }

        try {
            $this->connect();

            if (!$this->bindDn || !$this->bindPassword) {
                Log::error('[LDAP] searchByCpfService(): LDAP_BIND_DN/PASSWORD não configurados.');
                return null;
            }

            if (!@ldap_bind($this->conn, $this->bindDn, $this->bindPassword)) {
                Log::error('[LDAP] searchByCpfService(): bind serviço falhou', [
                    'bindDn' => $this->bindDn,
                    'errno' => ldap_errno($this->conn),
                    'error' => ldap_error($this->conn),
                ]);
                return null;
            }

            $upn = "{$cpf}@{$this->domain}";

            $filter =
                '(&(objectClass=user)(|' .
                    '(userPrincipalName=' . $this->escape($upn) . ')' .
                    '(sAMAccountName='    . $this->escape($cpf) . ')' .
                    '(employeeID='        . $this->escape($cpf) . ')' .
                    '(employeeNumber='    . $this->escape($cpf) . ')' .
                    '(cpf='               . $this->escape($cpf) . ')' .
                    '(extensionAttribute2=' . $this->escape($cpf) . ')' .
                '))';

            $attrs = [
                'cn','givenName','sn','displayName','mail',
                'sAMAccountName','userPrincipalName',
                'employeeNumber','employeeID',
                'extensionAttribute1','extensionAttribute2','extensionAttribute3',
                'title','department','company','physicalDeliveryOfficeName',
                'description','distinguishedName','memberOf'
            ];

            Log::info('[LDAP] searchByCpfService(): buscando', [
                'uri' => $this->ldapUri(),
                'baseDn' => $this->baseDn,
                'bindDn' => $this->bindDn,
                'cpf' => $cpf,
                'upn' => $upn,
                'filter' => $filter,
            ]);

            $sr = @ldap_search($this->conn, $this->baseDn, $filter, $attrs);

            if ($sr === false) {
                Log::error('[LDAP] searchByCpfService(): ldap_search falhou', [
                    'errno' => ldap_errno($this->conn),
                    'error' => ldap_error($this->conn),
                    'baseDn' => $this->baseDn,
                ]);
                return null;
            }

            $entries = ldap_get_entries($this->conn, $sr);
            $count = is_array($entries) ? ($entries['count'] ?? 0) : 0;

            Log::info('[LDAP] searchByCpfService(): resultado', ['count' => $count]);

            if (!is_array($entries) || $count < 1) {
                Log::info("[LDAP] searchByCpfService(): nenhum usuário encontrado para CPF {$cpf}.");
                return null;
            }

            $entry = $entries[0];

            // LDAP retorna keys em minúsculo; normalizamos
            $get = function (string $key) use ($entry) {
                $k = strtolower($key);
                if (!isset($entry[$k]) || !isset($entry[$k]['count'])) {
                    return null;
                }
                return ($entry[$k]['count'] > 0) ? $entry[$k][0] : null;
            };

            $nomeCompleto = $get('displayName') ?: ($get('cn') ?: null);
            $email        = $get('mail');
            $sam          = $get('sAMAccountName');
            $upnAttr      = $get('userPrincipalName');

            $matricula = $get('employeeNumber') ?: ($get('employeeID') ?: ($get('extensionAttribute2') ?: null));
            $unidade   = $get('department') ?: ($get('physicalDeliveryOfficeName') ?: ($get('company') ?: null));

            return array_filter([
                'nome'      => $nomeCompleto,
                'email'     => $email,
                'cpf'       => $cpf,
                'login'     => $sam ?: ($upnAttr ?: null),
                'matricula' => $matricula,
                'unidade'   => $unidade,
                'dn'        => $get('distinguishedName'),
            ]);
        } catch (\Throwable $e) {
            Log::error('[LDAP] searchByCpfService() exception: ' . $e->getMessage());
            return null;
        } finally {
            $this->close();
        }
    }

    /**
     * Diagnóstico rápido:
     * verifica se o bind técnico funciona e se o LDAP consegue listar usuários (contagem).
     */
    public function debugServiceCount(): array
    {
        try {
            $this->connect();

            if (!$this->bindDn || !$this->bindPassword) {
                return ['ok' => false, 'message' => 'LDAP_BIND_DN/PASSWORD não configurados.'];
            }

            if (!@ldap_bind($this->conn, $this->bindDn, $this->bindPassword)) {
                return [
                    'ok' => false,
                    'message' => 'Bind de serviço falhou.',
                    'bindDn' => $this->bindDn,
                    'errno' => ldap_errno($this->conn),
                    'error' => ldap_error($this->conn),
                ];
            }

            $sr = @ldap_search($this->conn, $this->baseDn, '(objectClass=user)', ['cn']);

            if ($sr === false) {
                return [
                    'ok' => false,
                    'message' => 'ldap_search falhou.',
                    'baseDn' => $this->baseDn,
                    'errno' => ldap_errno($this->conn),
                    'error' => ldap_error($this->conn),
                ];
            }

            $entries = ldap_get_entries($this->conn, $sr);
            $count = is_array($entries) ? ($entries['count'] ?? 0) : 0;

            return [
                'ok' => true,
                'message' => 'Search ok',
                'uri' => $this->ldapUri(),
                'baseDn' => $this->baseDn,
                'count' => $count,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        } finally {
            $this->close();
        }
    }

    /* ========================= Internals ========================= */

    protected function ldapUri(): string
    {
        // STARTTLS usa ldap://host:389 + ldap_start_tls()
        // LDAPS (636) seria ldaps://host:636 e NÃO usar ldap_start_tls()
        $scheme = 'ldap';
        return "{$scheme}://{$this->host}:{$this->port}";
    }

    protected function connect(): void
    {
        // PHP 8.4: ldap_connect(host, port) com 2 args foi deprecated.
        // Use URI.
        $this->conn = ldap_connect($this->ldapUri());

        if (!$this->conn) {
            throw new \RuntimeException("Falha ao conectar no LDAP {$this->ldapUri()}");
        }

        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);

        @ldap_set_option($this->conn, LDAP_OPT_NETWORK_TIMEOUT, 10);
        @ldap_set_option($this->conn, LDAP_OPT_TIMELIMIT, 10);

        if ($this->useTls) {
            if (!@ldap_start_tls($this->conn)) {
                $eno = ldap_errno($this->conn);
                $err = ldap_error($this->conn);
                throw new \RuntimeException("Falha ao iniciar STARTTLS (errno={$eno}): {$err}");
            }
        }
    }

    protected function close(): void
    {
        if ($this->conn) {
            @ldap_unbind($this->conn);
            $this->conn = null;
        }
    }

    /**
     * Escapa filtros LDAP (evita LDAP injection).
     */
    protected function escape(string $v): string
    {
        if (function_exists('ldap_escape')) {
            return ldap_escape($v, '', LDAP_ESCAPE_FILTER);
        }

        return addcslashes($v, '\\*()' . "\x00");
    }
}
