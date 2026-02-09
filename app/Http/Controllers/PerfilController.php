<?php

namespace App\Http\Controllers;

use App\Models\Opm;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe formulário de completar cadastro.
     */
    public function completar()
    {
        $user = auth()->user();

        $opms = Opm::query()
            ->orderBy('sigla')
            ->get(['id', 'sigla', 'nome', 'cidade']);

        return view('perfil.completar', [
            'user' => $user,
            'opms' => $opms,
        ]);
    }

    /**
     * Salva dados complementares e marca cadastro_completo.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nome'            => ['required', 'string', 'min:3', 'max:150'],
            'posto_graduacao' => ['required', 'string', 'min:2', 'max:30'],
            'numero_praca'    => ['required', 'string', 'min:1', 'max:20'],
            'rg_militar'      => ['required', 'string', 'min:1', 'max:20'],
            'matricula'       => ['required', 'string', 'min:1', 'max:50'],
            'telefone'        => ['required', 'string', 'min:10', 'max:20'],
            'opm_id'          => ['required', 'integer', 'exists:opms,id'],
        ], [
            'opm_id.exists' => 'Selecione uma unidade válida.',
        ]);

        // Normalizações
        $data['telefone'] = preg_replace('/\D+/', '', (string) $data['telefone']);
        $data['numero_praca'] = trim((string) $data['numero_praca']);
        $data['rg_militar'] = trim((string) $data['rg_militar']);
        $data['matricula'] = trim((string) $data['matricula']);
        $data['posto_graduacao'] = trim((string) $data['posto_graduacao']);

        // CPF é sempre do usuário logado (não vem do form)
        // Nunca permitir alteração de CPF por request
        unset($data['cpf']);

        // Atualiza perfil do usuário
        $user->fill($data);

        // Recalcula flag de completude
        $user->cadastro_completo = $user->isCadastroCompleto();

        // Ao completar, automaticamente vira "pending" para o admin aprovar
        if ($user->cadastro_completo) {
            if (($user->solicitacao_status ?? 'none') === 'none') {
                $user->solicitacao_status = 'pending';
            }
        }

        // Nunca libera aqui (isso é papel do admin)
        $user->permitido = false;

        $user->save();

        return redirect()
            ->route('home.restrita')
            ->with('success', 'Cadastro enviado! Aguarde a aprovação do administrador.');
    }
}
