<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    protected $password = 'password';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'cpf';
    }

    // ✅ ÚNICA credentials() — já com 'permitido'
    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->{$this->username()},
            'password' => $request->password,
            'permitido' => true,
        ];
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (Auth::attempt($this->credentials($request), $request->has('remember'))) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            $this->password => 'required|string',
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isAdmin() || $user->isP4()) {
            return redirect()->route('home');
        }
        return redirect(RouteServiceProvider::HOME);
    }

    protected function loggedOut(Request $request)
    {
        return redirect()->route('home');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
    $user = \App\Models\Usuario::where($this->username(), $request->{$this->username()})->first();

    // Se o CPF existe mas o usuário está bloqueado:
    if ($user && !$user->permitido) {
        throw ValidationException::withMessages([
            $this->username() => ['Seu acesso está bloqueado. Procure o administrador do sistema.'],
        ]);
        }
        // Caso geral: CPF errado ou senha errada
        throw ValidationException::withMessages([
           $this->username() => [trans('auth.failed')],
        ]);
    }
}
