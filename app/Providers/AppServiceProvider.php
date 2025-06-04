<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; // Importa a classe base
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //$this->registerPolicies();

        Gate::define('isAdmin', function (Usuario $user) {
            return $user->isAdmin();
        });

        Gate::define('isP4', function (Usuario $user) {
            return $user->isP4();
        });
    }
}