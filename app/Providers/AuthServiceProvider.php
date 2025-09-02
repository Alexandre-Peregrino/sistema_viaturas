<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Ldap\LdapProvider;

// Models
use App\Models\Usuario;
use App\Models\Veiculo;
// (Opcional) Se for usar policies também para rádios e manutenções, descomente:
// use App\Models\Radio;
// use App\Models\Manutencao;

// Policies
use App\Policies\ViaturaPolicy;
// (Opcional) Se criar policies análogas, descomente:
// use App\Policies\RadioPolicy;
// use App\Policies\ManutencaoPolicy;
use App\Models\Manutencao;
use App\Policies\ManutencaoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapear Models → Policies
     */
    protected $policies = [
        Veiculo::class => ViaturaPolicy::class,
        //Radio::class => RadioPolicy::class,
        Manutencao::class => ManutencaoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Registro do driver de autenticação LDAP
        Auth::provider('ldap', function ($app, array $config) {
            return new LdapProvider($app['hash'], $config['model']);
        });

        // Gates de perfil (úteis para menus/links e checks simples)
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
