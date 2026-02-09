<?php

namespace App\Ldap;

use App\Models\Usuario;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class LdapProvider extends EloquentUserProvider
{
    /**
     * Whitelist local:
     * - só retorna usuário se existir no banco E estiver autorizado.
     */
    public function retrieveByCredentials(array $credentials)
    {
        $cpf = preg_replace('/\D+/', '', (string)($credentials['cpf'] ?? ''));

        if ($cpf === '') {
            return null;
        }

        $q = Usuario::query()
            ->where('cpf', $cpf)
            ->where('permitido', true);

        // Se você tiver também um "ativo" (recomendado), descomente:
        // $q->where('ativo', true);

        // Se você usa Spatie Permission e quer exigir que tenha role:
        // $q->whereHas('roles');

        return $q->first();
    }

    /**
     * Senha validada no AD (bind).
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $authldap = new Authldap();

        // Garante que o CPF usado no bind vem do usuário local (não confia no request)
        $credentials['cpf'] = method_exists($user, 'getAttribute')
            ? $user->getAttribute('cpf')
            : ($credentials['cpf'] ?? null);

        return $authldap->autenticar($credentials);
    }
}
