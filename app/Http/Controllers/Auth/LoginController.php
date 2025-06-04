<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Mostrar form de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Processar login
    public function login(Request $request)
    {
        // Validar entrada
        $request->validate([
            'cpf' => 'required|string',
            'senha' => 'required|string',
        ]);

        // Tentar autenticar
        $credentials = [
            'cpf' => $request->cpf,
            'password' => $request->senha,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // O redirecionamento será tratado por authenticated()
            return redirect()->intended();
        }

        return back()->withErrors([
        'cpf' => 'CPF ou senha inválidos.',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
    public function username()
    {
        return 'cpf'; // ou 'email' se for o caso
    }
    protected function authenticated(Request $request, $user)
    {
        if ($user->isAdmin()) {
         return redirect()->route('admin.viaturas.index'); // Alinha com a rota existente
        }

        if ($user->isP4()) {
            return redirect()->route('p4.viaturas.index'); // Alinha com a rota existente
        }

        // Se o perfil for inválido, desloga e retorna ao login
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/login')->withErrors([
        'perfil' => 'Perfil de usuário inválido.',
    ]);
}

}
