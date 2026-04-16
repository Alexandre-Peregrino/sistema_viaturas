<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Ldap\Authldap;
use App\Models\Usuario;
use App\Services\RotawebAuthService;   // ← usamos o RotaWeb depois do LDAP
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;
    protected $password = 'password';

    public function __construct(
        private Authldap $ldap,
        private RotawebAuthService $rotaweb  // injetado
    ) {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'cpf';
    }

    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->{$this->username()},
            'password'        => $request->password,
        ];
    }

    public function login(Request $request)
    {
        // Validação rápida
        $this->validateLogin($request);

        $cpfRaw   = (string) $request->{$this->username()};
        $cpf      = preg_replace('/\D+/', '', $cpfRaw ?? '');
        $password = (string) $request->input('password');
        $remember = (bool) $request->boolean('remember');

        // 1) Autentica no AD/LDAP — passo obrigatório
        if (!$this->ldap->autenticar(['cpf' => $cpf, 'password' => $password])) {
            return back()
                ->withErrors([$this->username() => 'Credenciais inválidas no AD.'])
                ->withInput($request->except('password'));
        }

        // 2) Busca (ou cria) usuário local por CPF
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Usuario::where('cpf', $cpf)->first();

        if (!$usuario) {
            $usuario = new Usuario();
            $usuario->cpf  = $cpf;
            $usuario->nome = 'Usuário';

            if (schema_has_column('usuarios', 'perfil') && empty($usuario->perfil)) {
                $usuario->perfil = 'p4'; // default
            }
            if (schema_has_column('usuarios', 'permitido') && $usuario->permitido === null) {
                $usuario->permitido = true; // não bloquear; admin pode revogar
            }
            if (schema_has_column('usuarios', 'email') && empty($usuario->email)) {
                $usuario->email = $cpf . '@placeholder.local';
            }
            if (schema_has_column('usuarios', 'password') && empty($usuario->password)) {
                $usuario->password = Hash::make(Str::random(32)); // login é via LDAP
            }

            $usuario->save();
        }

        // 3) (RÁPIDO) Tenta coletar dados do RotaWeb (não bloqueia login se falhar)
        try {
            // Dica: reduza o timeout em config/rotaweb.php → 'timeout' => 6..8
            $ret = $this->rotaweb->attempt($cpf, $password);

            $rwUser = $ret['usuario']     ?? [];
            $norm   = $ret['normalized']  ?? [];

            // Extrai campos leves
            $nome        = $norm['nome']         ?? ($rwUser['usuario_nome'] ?? null);
            $nomeGuerra  = $norm['nome_guerra']  ?? ($rwUser['usuario_nome_guerra'] ?? null);
            $matricula   = $norm['matricula']    ?? ($rwUser['usuario_matricula'] ?? null);
            $titulo      = $norm['titulo']       ?? ($rwUser['usuario_titulo'] ?? null);

            // E-mail: tenta normalized, depois bruto; valida
            $emailRW    = $norm['email'] ?? ($rwUser['email'] ?? null);
            if (is_string($emailRW)) {
                $emailRW = trim($emailRW);
                if (!filter_var($emailRW, FILTER_VALIDATE_EMAIL)) {
                    $emailRW = null;
                } else {
                    $emailRW = strtolower($emailRW);
                }
            } else {
                $emailRW = null;
            }

            // Atualiza apenas o que veio e mudou
            $dirty = false;

            if ($nome && $usuario->nome !== $nome) {
                $usuario->nome = $nome; $dirty = true;
            }
            if (schema_has_column('usuarios', 'nome_guerra') && $nomeGuerra && $usuario->nome_guerra !== $nomeGuerra) {
                $usuario->nome_guerra = $nomeGuerra; $dirty = true;
            }
            if (schema_has_column('usuarios', 'matricula') && $matricula && $usuario->matricula !== $matricula) {
                $usuario->matricula = $matricula; $dirty = true;
            }
            if (schema_has_column('usuarios', 'titulo') && $titulo && $usuario->titulo !== $titulo) {
                $usuario->titulo = $titulo; $dirty = true;
            }
            if (schema_has_column('usuarios', 'email') && $emailRW) {
                $isPlaceholder = str_ends_with((string) $usuario->email, '@placeholder.local');
                if ($isPlaceholder || $usuario->email !== $emailRW) {
                    $usuario->email = $emailRW; $dirty = true;
                }
            }

            if ($dirty) {
                $usuario->save();
            }

        } catch (\Throwable $e) {
            // Não interrompe login; só loga para diagnóstico
            Log::info('RotaWeb pós-LDAP falhou/timeout — seguindo sem travar login', [
                'cpf' => $cpf,
                'err' => $e->getMessage(),
            ]);
        }

        // 4) Aviso opcional se houver coluna "permitido" e estiver false (sem bloquear)
        if (schema_has_column('usuarios', 'permitido') && !$usuario->permitido) {
            session()->flash('warning', 'Seu acesso ainda aguarda liberação do administrador. Alguns recursos podem não estar disponíveis.');
        }

        // 5) Login Laravel (rápido) + redirect
        Auth::login($usuario, $remember);
        return redirect()->intended(route('home'));
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            $this->password   => 'required|string',
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}

/**
 * Helper: checa se a coluna existe antes de usar (evita exceções)
 */
if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            Log::warning('schema_has_column falhou', ['table' => $table, 'column' => $column, 'err' => $e->getMessage()]);
            return false;
        }
    }
}
