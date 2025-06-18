<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Lista usuários conforme perfil.
     */
    public function index()
    {
        $usuarioLogado = auth()->user();

        if ($usuarioLogado->isAdmin()) {
            $usuarios = Usuario::with('opm')->get();
        } elseif ($usuarioLogado->isP4()) {
            $usuarios = Usuario::with('opm')->where('opm_id', $usuarioLogado->opm_id)->get();
        } else {
            abort(403, 'Acesso não autorizado.');
        }

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $opms = Opm::all();
        $perfis = ['admin', 'p4'];

        return view('admin.usuarios.create', compact('opms', 'perfis'));
    }

    /**
     * Salva novo usuário.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cpf' => [
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]+$/',
                Rule::unique('usuarios', 'cpf'),
            ],
            'nome' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email'),
            ],
            'perfil' => 'required|string|in:admin,p4',
            'password' => 'required|string|min:6|confirmed',
            'opm_id' => 'required|exists:opms,id',
        ], [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'nome.required' => 'O nome é obrigatório.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'perfil.required' => 'O perfil é obrigatório.',
            'perfil.in' => 'O perfil deve ser Admin ou P4.',

            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere!',

            'opm_id.required' => 'Selecione uma OPM.',
            'opm_id.exists' => 'A OPM selecionada não é válida.',
        ]);

        Usuario::create([
            'cpf' => $request->cpf,
            'nome' => $request->nome,
            'email' => $request->email,
            'perfil' => $request->perfil,
            'password' => $request->password, // Mutator faz o hash automático
            'opm_id' => $request->opm_id,
            'permitido' => true,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Formulário de edição.
     */
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $opms = Opm::all();
        $perfis = ['admin', 'p4'];

        return view('admin.usuarios.edit', compact('usuario', 'opms', 'perfis'));
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $rules = [
            'cpf' => [
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]+$/',
                Rule::unique('usuarios', 'cpf')->ignore($usuario->id),
            ],
            'nome' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario->id),
            ],
            'perfil' => 'required|string|in:admin,p4',
            'opm_id' => 'required|exists:opms,id',
            'permitido' => 'required|boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $request->validate($rules, [
            'cpf.unique' => 'Este CPF já está cadastrado para outro usuário.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.regex' => 'O CPF deve conter apenas números.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado para outro usuário.',

            'password.confirmed' => 'A confirmação da nova senha não confere!',
            'password.min' => 'A nova senha deve ter no mínimo 6 caracteres.',
        ]);

        $usuario->cpf = $request->cpf;
        $usuario->nome = $request->nome;
        $usuario->email = $request->email;
        $usuario->perfil = $request->perfil;
        $usuario->opm_id = $request->opm_id;
        $usuario->permitido = $request->permitido;

        if ($request->filled('password')) {
            $usuario->password = $request->password; // Mutator faz o hash automático
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove o usuário.
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
