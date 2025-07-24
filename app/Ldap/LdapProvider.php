<?php

namespace App\Ldap;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class LdapProvider extends EloquentUserProvider
{
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $authldap = new Authldap();
        return $authldap->autenticar($credentials);
    }
}
