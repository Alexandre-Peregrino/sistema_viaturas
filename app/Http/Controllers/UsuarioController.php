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

        $busca = trim((string) $request->input('busca'));

        $usuarios = Usuario::with('opm')
            ->when($busca, function ($query, $busca) {
                $busca = mb_strtolower($busca);
                $query->where(function ($q) use ($busca) {
                    $q->whereRaw('LOWER(nome) LIKE ?', ["%{$busca}%"])
                      ->orWhereRaw('cpf LIKE ?', ["%{$busca}%"])
                      ->orWhereRaw('matricula LIKE ?', ["%{$busca}%"]);
                });
            })
            ->orderBy('nome')
            ->paginate(20)
            ->appends(['busca' => $busca]);

        return view('admin.usuarios.index', compact('usuarios', 'busca'));
    }

    /**
     * Formulário de criação — desativado (sem criação manual).
     */
    public function create()
    {
        abort(404);
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
     */
    public function edit(Usuario $usuario)
    {
        $this->ensureAdmin();

        $opms   = Opm::orderBy('sigla')->get();
        $perfis = ['admin', 'p4']; // se tiver super_admin, inclua aqui

        return view('admin.usuarios.edit', compact('usuario', 'opms', 'perfis'));
    }

    /**
     * Atualiza dados do usuário no banco local (somente Admin/Super Admin).
     * Agora editáveis:
     * - Dados pessoais/funcionais: nome, nome_guerra, matricula, titulo, posto_graduacao, numero_praca, rg_militar, telefone
     * - Permissões locais: perfil, opm_id, permitido
     * - E-mail
     * - Controle: cadastro_completo, solicitacao_status (se você liberou na view)
     *
     * Observação: CPF segue somente leitura por padrão.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            // --- Dados do usuário (banco local) ---
            'nome'            => ['required', 'string', 'max:255'],
            'nome_guerra'     => ['nullable', 'string', 'max:120'],
            'matricula'       => ['nullable', 'string', 'max:50'],
            'titulo'          => ['nullable', 'string', 'max:80'],
            'posto_graduacao' => ['nullable', 'string', 'max:80'],
            'numero_praca'    => ['nullable', 'string', 'max:30'],
            'rg_militar'      => ['nullable', 'string', 'max:50'],
            'telefone'        => ['nullable', 'string', 'max:30'],

            // (CPF travado)
            // 'cpf' => ['nullable', 'string', 'max:14'],

            // --- Permissões locais ---
            'perfil'    => ['required', Rule::in(['admin', 'p4'])],
            'opm_id'    => ['required', 'exists:opms,id'],
            'permitido' => ['sometimes', 'boolean'],

            // --- Login/contato ---
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario->id),
            ],

            // --- Flags de cadastro (se existirem no seu schema) ---
            'cadastro_completo'  => ['sometimes', 'boolean'],
            'solicitacao_status' => ['nullable', 'string', 'max:50'],
        ], [
            'nome.required'      => 'O nome é obrigatório.',
            'perfil.required'    => 'Selecione o perfil.',
            'perfil.in'          => 'O perfil deve ser_toggle_admin ou P4.',
            'opm_id.required'    => 'Selecione a OPM.',
            'opm_id.exists'      => 'A OPM selecionada não é válida.',
            'email.required'     => 'O e-mail é obrigatório.',
            'email.email'        => 'Informe um e-mail válido.',
            'email.unique'       => 'Este e-mail já está em uso por outro usuário.',
        ]);

        // Normalizações simples
        $validated['email'] = strtolower($validated['email']);

        // Se quiser padronizar nome_guerra em maiúsculas:
        if (array_key_exists('nome_guerra', $validated) && $validated['nome_guerra'] !== null) {
            $validated['nome_guerra'] = mb_strtoupper($validated['nome_guerra']);
        }

        // Atualiza "permitido" apenas se vier no request
        if ($request->has('permitido')) {
            $validated['permitido'] = $request->boolean('permitido');
        } else {
            unset($validated['permitido']);
        }

        // Atualiza cadastro_completo apenas se vier no request
        if ($request->has('cadastro_completo')) {
            $validated['cadastro_completo'] = $request->boolean('cadastro_completo');
        } else {
            unset($validated['cadastro_completo']);
        }

        // CPF travado (garantia extra)
        unset($validated['cpf']);

        $usuario->fill($validated);
        $usuario->save();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
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
