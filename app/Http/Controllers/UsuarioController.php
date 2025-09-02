<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Lista usuários (somente Admin).
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $busca = trim($request->input('busca'));

        $usuarios = Usuario::with('opm')
            ->when($busca, function ($query, $busca) {
                $busca = mb_strtolower($busca);
                $query->whereRaw('LOWER(nome) LIKE ?', ["%{$busca}%"])
                    ->orWhereRaw('cpf LIKE ?', ["%{$busca}%"])
                    ->orWhereRaw('matricula LIKE ?', ["%{$busca}%"]);
            })
            ->orderBy('nome')
            ->paginate(20)
            ->appends(['busca' => $busca]); // mantém a busca na paginação

        return view('admin.usuarios.index', compact('usuarios', 'busca'));
    }


    /**
     * Formulário de criação — desativado (sem criação manual).
     */
    public function create()
    {
        abort(404); // rota removida; manter por segurança caso alguém chame direto
    }

    /**
     * Armazenar novo usuário — desativado (sem criação manual).
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Formulário de edição (somente Admin).
     * Observação: agora o e-mail é editável pelo admin.
     */
    public function edit(Usuario $usuario)
    {
        $this->ensureAdmin();

        $opms   = Opm::orderBy('sigla')->get();
        $perfis = ['admin', 'p4'];

        return view('admin.usuarios.edit', compact('usuario', 'opms', 'perfis'));
    }

    /**
     * Atualiza permissões do usuário (somente Admin).
     * Editáveis: perfil, opm_id, permitido (opcional) e email.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'perfil'    => ['required', Rule::in(['admin', 'p4'])],
            'opm_id'    => ['required', 'exists:opms,id'],
            'permitido' => ['sometimes', 'boolean'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
        ], [
            'perfil.required' => 'Selecione o perfil.',
            'perfil.in'       => 'O perfil deve ser Admin ou P4.',
            'opm_id.required' => 'Selecione a OPM.',
            'opm_id.exists'   => 'A OPM selecionada não é válida.',
            'email.required'  => 'O e-mail é obrigatório.',
            'email.email'     => 'Informe um e-mail válido.',
            'email.unique'    => 'Este e-mail já está em uso por outro usuário.',
        ]);

        // Atualiza sempre
        $usuario->perfil = $validated['perfil'];
        $usuario->opm_id = $validated['opm_id'];
        $usuario->email  = strtolower($validated['email']);

        // Atualiza "permitido" apenas se vier no request
        if ($request->has('permitido')) {
            $usuario->permitido = $request->boolean('permitido');
        }

        $usuario->save();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Permissões e e-mail atualizados com sucesso!');
    }

    /**
     * Remove o usuário (somente Admin).
     */
    public function destroy(Usuario $usuario)
    {
        $this->ensureAdmin();

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    /* ========================== Helpers ========================== */

    private function ensureAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Ação permitida apenas para administradores.');
        }
    }
}
