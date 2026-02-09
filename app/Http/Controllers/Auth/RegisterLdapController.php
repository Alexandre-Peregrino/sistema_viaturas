<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\Authldap;
use App\Models\Usuario;
use App\Models\Opm;

class RegisterLdapController extends Controller
{
    public function create()
    {
        return view('auth.register_ldap');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cpf'      => ['required','string','regex:/^\d{11}$/'],
            // 'nome'  => removido (vamos priorizar o AD)
            'password' => ['required','string','min:4'], // senha do AD (não será salva)
        ], [
            'cpf.regex' => 'Informe o CPF com 11 dígitos (somente números).',
        ]);

        $cpf = preg_replace('/\D+/', '', $data['cpf']);

        // 1) Autentica no AD
        $ldap = new Authldap();
        if (!$ldap->autenticar(['cpf' => $cpf, 'password' => $data['password']])) {
            return back()->withErrors(['password' => 'CPF ou senha do AD inválidos.'])
                         ->withInput($request->except('password'));
        }

        // 2) Busca atributos no AD (conta de serviço)
        $attrs = $ldap->searchByCpfService($cpf) ?? [];

        $nome      = $attrs['nome']      ?? null;
        $email     = $attrs['email']     ?? null;
        $matricula = $attrs['matricula'] ?? null;
        $unidadeStr = $attrs['unidade']  ?? null;

        // 3) Mapeamento de OPM (CONSERVADOR para não "furar")
        // Se você não confia no campo "unidade", é melhor deixar null.
        $opmId = null;
        if ($unidadeStr) {
            $opmId = Opm::query()
                ->where('sigla', $unidadeStr) // somente match exato
                ->value('id');
        }

        // 4) Cria/atualiza usuário local mínimo
        // - permitido = false: aguarda liberação do admin
        // - perfil padrão: ajuste conforme seu gate/policy (P4 vs p4)
        $usuario = Usuario::updateOrCreate(
            ['cpf' => $cpf],
            [
                'nome'      => $nome ?? 'Usuário', // fallback se AD não retornar
                'email'     => $email,
                'matricula' => $matricula,
                'opm_id'    => $opmId,
                'perfil'    => 'P4',
                'permitido' => false,
            ]
        );

        return redirect()->route('login')->with('success',
            'Cadastro enviado! Um administrador precisa liberar seu acesso.'
        );
    }
}
