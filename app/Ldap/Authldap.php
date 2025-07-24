<?php

namespace App\Ldap;

use Illuminate\Support\Facades\Log;

class Authldap
{
    protected $ldap_dn;
    protected $ldap_password;
    protected $ldap_con;
    protected $nome_ad = 'pm.govrn';      // ← DOMÍNIO DO AD
    protected $ldap_ip = '10.9.192.95';   // ← IP DO SERVIDOR LDAP

    public function autenticar($credenciais)
    {
        $cpf = preg_replace('/\D+/', '', $credenciais['cpf']);
        $senha = $credenciais['password'];

        $this->ldap_dn = $cpf . '@' . $this->nome_ad;
        $this->ldap_password = $senha;

        $this->ldap_con = ldap_connect($this->ldap_ip);

        if (!$this->ldap_con) {
            Log::error("Falha ao conectar ao servidor LDAP.");
            return false;
        }

        ldap_set_option($this->ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

        try {
            if (ldap_bind($this->ldap_con, $this->ldap_dn, $this->ldap_password)) {
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Erro LDAP: " . $e->getMessage());
        }

        return false;
    }
}
