<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     * Altera o campo de login padrão de 'email' para 'cpf'.
     *
     * @return string
     */
    public function username()
    {
        return 'cpf';
    }

    /**
     * O usuário foi autenticado com sucesso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        // Redireciona usuários admin e P4 para a rota 'home' após o login
        if ($user->isAdmin() || $user->isP4()) {
            return redirect()->route('home');
        }
        // Fallback para outros tipos de usuários ou para a home padrão
        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * O usuário fez logout da aplicação.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('home');
    }
}
