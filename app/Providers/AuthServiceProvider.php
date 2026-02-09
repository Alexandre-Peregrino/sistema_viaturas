<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use App\Ldap\LdapProvider;

// Models
use App\Models\Usuario;
use App\Models\Veiculo;
use App\Models\Manutencao;

// Policies
use App\Policies\ViaturaPolicy;
use App\Policies\ManutencaoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapear Models → Policies
     */
    protected $policies = [
        Veiculo::class   => ViaturaPolicy::class,
        Manutencao::class => ManutencaoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Registro do driver de autenticação LDAP (provider custom).
         * Esse nome ("ldap_eloquent") deve bater com config/auth.php.
         */
        Auth::provider('ldap_eloquent', function ($app, array $config) {
            return new LdapProvider($app['hash'], $config['model']);
        });

        // Gates de perfil
        Gate::define('isAdmin', function (Usuario $user) {
            return $user->isAdmin();
        });

        Gate::define('isP4', function (Usuario $user) {
            return $user->isP4();
        });

        // Ex.: consultas ao SISGP/RotaWeb liberadas para Admin e P4
        Gate::define('consultarSisgp', function (Usuario $user) {
            return $user->isAdmin() || $user->isP4();
        });
    }
}
