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
     *  — usado no fluxo de autenticação durante o login.
     * ============================================================ */
    public function autenticar(array $credenciais): bool
    {
        $cpf   = preg_replace('/\D+/', '', $credenciais['cpf'] ?? '');
        $senha = $credenciais['password'] ?? '';

        if ($cpf === '' || $senha === '') {
            Log::warning('[LDAP] autenticar(): CPF ou senha vazios.');
            return false;
        }

        $userUpn = "{$cpf}@{$this->domain}";

        try {
            $this->connect();

            // Bind direto com o usuário
            if (@ldap_bind($this->conn, $userUpn, $senha)) {
                return true;
            }

            $err = ldap_error($this->conn);
            Log::warning("[LDAP] autenticar() falhou para {$userUpn}: {$err}");
            return false;
        } catch (\Throwable $e) {
            Log::error("[LDAP] autenticar() exception: " . $e->getMessage());
            return false;
        } finally {
            $this->close();
        }
    }

    /* ============================================================
     *  Busca por CPF usando conta de serviço (bind técnico)
     *  — usado para pré-preencher dados cadastrais.
     *  Retorna array “flat” (nome, email, matricula, unidade, …)
     *  ou null se não encontrar.
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

            // Bind com conta técnica
            if (!$this->bindDn || !$this->bindPassword) {
                Log::error('[LDAP] searchByCpfService(): LDAP_BIND_DN/PASSWORD não configurados.');
                return null;
            }
            if (!@ldap_bind($this->conn, $this->bindDn, $this->bindPassword)) {
                $err = ldap_error($this->conn);
                Log::error("[LDAP] Bind de serviço falhou (DN={$this->bindDn}): {$err}");
                return null;
            }

            // Alguns diretórios guardam CPF em atributos diferentes; tentamos todos
            $upn = "{$cpf}@{$this->domain}";
            $filter = "(&
                (objectClass=user)
                (|
                    (userPrincipalName={$this->escape($upn)})
                    (sAMAccountName={$this->escape($cpf)})
                    (employeeNumber={$this->escape($cpf)})
                    (cpf={$this->escape($cpf)})
                    (extensionAttribute2={$this->escape($cpf)})
                )
            )";

            $attrs = [
                'cn','givenName','sn','displayName','mail',
                'sAMAccountName','userPrincipalName',
                'employeeNumber','employeeID',
                'extensionAttribute1','extensionAttribute2','extensionAttribute3',
                'title','department','company','physicalDeliveryOfficeName',
                'description','distinguishedName','memberOf'
            ];

            $sr = @ldap_search($this->conn, $this->baseDn, $filter, $attrs);
            if ($sr === false) {
                $err = ldap_error($this->conn);
                Log::error("[LDAP] searchByCpfService(): ldap_search falhou: {$err}");
                return null;
            }

            $entries = ldap_get_entries($this->conn, $sr);
            if ($entries === false || ($entries['count'] ?? 0) < 1) {
                Log::info("[LDAP] searchByCpfService(): nenhum usuário encontrado para CPF {$cpf}.");
                return null;
            }

            // Pega o primeiro resultado “bom”
            $entry = $entries[0];

            // Helpers para pegar o primeiro valor do atributo
            $get = function(string $key) use ($entry) {
                if (!isset($entry[$key])) return null;
                if (!isset($entry[$key]['count'])) return null;
                return $entry[$key]['count'] > 0 ? $entry[$key][0] : null;
            };

            $nomeCompleto = $get('displayname') ?: ($get('cn') ?: null);
            $email        = $get('mail');
            $sam          = $get('samaccountname');
            $upnAttr      = $get('userprincipalname');

            // Onde está a matrícula? Muitas vezes em employeeNumber / employeeID / extensionAttribute2
            $matricula = $get('employeenumber') ?: ($get('employeeid') ?: ($get('extensionattribute2') ?: null));

            // Unidade pode vir de vários lugares — usamos algo aproximado
            $unidade = $get('department') ?: ($get('physicaldeliveryofficename') ?: ($get('company') ?: null));

            return array_filter([
                'nome'        => $nomeCompleto,
                'email'       => $email,
                'cpf'         => $cpf,
                'login'       => $sam ?: ($upnAttr ?: null),
                'matricula'   => $matricula,
                'unidade'     => $unidade,
                'dn'          => $get('distinguishedname'),
            ]);
        } catch (\Throwable $e) {
            Log::error("[LDAP] searchByCpfService() exception: " . $e->getMessage());
            return null;
        } finally {
            $this->close();
        }
    }

    /* ========================= Internals ========================= */

    protected function connect(): void
    {
        $this->conn = ldap_connect($this->host, $this->port);
        if (!$this->conn) {
            throw new \RuntimeException("Falha ao conectar no LDAP {$this->host}:{$this->port}");
        }

        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0); // evita referrals chateando
        // timeouts de rede (opcional)
        @ldap_set_option($this->conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if ($this->useTls) {
            if (!@ldap_start_tls($this->conn)) {
                $err = ldap_error($this->conn);
                throw new \RuntimeException("Falha ao iniciar STARTTLS: {$err}");
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

    /** Escapa filtros LDAP simples */
    protected function escape(string $v): string
    {
        return addcslashes($v, '\\*()' . "\x00");
    }
}
