<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth; // ← adicione esta linha
use App\Ldap\LdapProvider;            // ← adicione esta linha
use App\Models\Usuario;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Registro do driver de autenticação LDAP
        Auth::provider('ldap', function ($app, array $config) {
            return new LdapProvider($app['hash'], $config['model']);
        });

        // Definições de permissões
        Gate::define('isAdmin', function (Usuario $user) {
            return $user->isAdmin();
        });

        Gate::define('isP4', function (Usuario $user) {
            return $user->isP4();
        });
    }
}
