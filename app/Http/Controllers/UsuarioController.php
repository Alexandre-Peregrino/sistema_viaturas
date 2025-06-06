<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class UsuarioController extends Controller
{
    // ADMIN: Lista todos os usuários
    public function index()
    {
        $usuarios = Usuario::with('opm')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    // ADMIN: Mostra formulário de criação
    public function create()
    {
        $opms = Opm::all();
        return view('admin.usuarios.create', compact('opms'));
    }

    // ADMIN: Armazena novo usuário
    public function store(Request $request)
    {
        $request->validate([
            'cpf' => 'required|unique:usuarios,cpf',
            'nome' => 'required',
            'perfil' => 'required|in:admin,p4',
            'senha' => 'required|min:6',
            'opm_id' => 'required|exists:opms,id'
        ]);

        Usuario::create([
            'cpf' => $request->cpf,
            'nome' => $request->nome,
            'perfil' => $request->perfil,
            'senha' => Hash::make($request->senha),
            'permitido' => $request->has('permitido'),
            'opm_id' => $request->opm_id,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    // ADMIN: Formulário de edição
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $opms = Opm::all();
        return view('admin.usuarios.edit', compact('usuario', 'opms'));
    }

    // ADMIN: Atualiza usuário
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'cpf' => 'required|unique:usuarios,cpf,' . $usuario->id,
            'nome' => 'required',
            'perfil' => 'required|in:admin,p4',
            'opm_id' => 'required|exists:opms,id'
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

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // ADMIN: Remove usuário
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return redirect()->route('admin.usuarios.index')->with('success', 'Usuário removido com sucesso!');
    }

    // P4: Lista apenas usuários da mesma OPM
    public function meusUsuarios()
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = auth()->user();

        $usuarios = Usuario::where('opm_id', $usuario->opm_id)->get();
        return view('p4.usuarios.index', compact('usuarios'));
    }
}
