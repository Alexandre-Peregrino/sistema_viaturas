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
            'nome'     => ['required','string','max:150'],
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

        // 2) (Opcional) Busca atributos no AD p/ enriquecer cadastro
        $attrs = method_exists($ldap, 'searchByCpf') ? ($ldap->searchByCpf($cpf) ?? []) : [];
        $email      = $attrs['email']     ?? null;
        $matricula  = $attrs['matricula'] ?? null;
        $unidadeStr = $attrs['unidade']   ?? null;

        // Tentar mapear unidade do AD para sua tabela OPM (se existir correspondência)
        $opmId = null;
        if ($unidadeStr) {
            $opmId = Opm::query()
                ->where('sigla', $unidadeStr)
                ->orWhere('nome', 'ilike', '%'.$unidadeStr.'%') // Postgres
                ->value('id');
        }

        // 3) Cria (ou atualiza nome/email/matricula) o usuário local
        $usuario = Usuario::updateOrCreate(
            ['cpf' => $cpf],
            [
                'nome'      => $data['nome'],                 // usa o que veio do formulário
                'email'     => $email,
                'matricula' => $matricula,
                'opm_id'    => $opmId,
                'perfil'    => 'p4',                          // padrão (admin ajusta depois)
                'permitido' => false,                         // aguarda liberação do admin
            ]
        );

        return redirect()->route('login')->with('success',
            'Cadastro enviado! Um administrador precisa liberar seu acesso.'
        );
    }
}
