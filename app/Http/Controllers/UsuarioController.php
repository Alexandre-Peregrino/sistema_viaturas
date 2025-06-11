<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Lista os usuários (admin vê todos, p4 vê da sua OPM)
    public function index()
    {
        $usuarioLogado = auth()->user();

        if ($usuarioLogado->isAdmin()) {
            $usuarios = Usuario::with('opm')->get();
        } elseif ($usuarioLogado->isP4()) {
            $usuarios = Usuario::with('opm')
                ->where('opm_id', $usuarioLogado->opm_id)
                ->get();
        } else {
            abort(403, 'Acesso não autorizado');
        }

        return view('admin.usuarios.index', compact('usuarios'));
    }

    // Mostra formulário de criação de usuário
    public function create()
    {
        $opms = Opm::all();
        return view('admin.usuarios.create', compact('opms'));
    }

    // Armazena novo usuário
    public function store(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string|unique:usuarios,cpf',
            'nome' => 'required|string',
            'perfil' => 'required|in:admin,p4',
            'senha' => 'required|string|min:6',
            'opm_id' => 'required|exists:opms,id',
        ]);

        Usuario::create([
            'cpf' => $request->cpf,
            'nome' => $request->nome,
            'perfil' => $request->perfil,
            'senha' => Hash::make($request->senha),
            'permitido' => $request->has('permitido'),
            'opm_id' => $request->opm_id,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário cadastrado com sucesso.');
    }

    // Mostra formulário de edição
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $opms = Opm::all();
        return view('admin.usuarios.edit', compact('usuario', 'opms'));
    }

    // Atualiza o usuário
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'cpf' => 'required|string|unique:usuarios,cpf,' . $usuario->id,
            'nome' => 'required|string',
            'perfil' => 'required|in:admin,p4',
            'senha' => 'nullable|string|min:6',
            'opm_id' => 'required|exists:opms,id',
        ]);

        $usuario->cpf = $request->cpf;
        $usuario->nome = $request->nome;
        $usuario->perfil = $request->perfil;
        $usuario->opm_id = $request->opm_id;
        $usuario->permitido = $request->has('permitido');

        if ($request->filled('senha')) {
            $usuario->senha = Hash::make($request->senha);
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    // Exclui um usuário
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário excluído com sucesso.');
    }
}
