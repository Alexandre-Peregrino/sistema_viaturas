<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class ManutencaoController extends Controller
{
    // ADMIN: Lista todas as manutenções
    public function index()
    {
        $manutencoes = Manutencao::with('veiculo')->get();
        return view('admin.manutencoes.index', compact('manutencoes'));
    }

    // ADMIN: Formulário de criação
    public function create()
    {
        $veiculos = Veiculo::all();
        return view('admin.manutencoes.create', compact('veiculos'));
    }

    // ADMIN: Armazena nova manutenção
    public function store(Request $request)
    {
        $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'descricao' => 'required|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'tipo' => 'required|in:preventiva,corretiva',
            'valor' => 'nullable|numeric',
            'oficina' => 'nullable|string',
            'status' => 'required|string',
        ]);

        Manutencao::create($request->all());

        return redirect()->route('admin.manutencoes.index')->with('success', 'Manutenção cadastrada com sucesso!');
    }

    // ADMIN: Formulário de edição
    public function edit($id)
    {
        $manutencao = Manutencao::findOrFail($id);
        $veiculos = Veiculo::all();
        return view('admin.manutencoes.edit', compact('manutencao', 'veiculos'));
    }

    // ADMIN: Atualiza manutenção
    public function update(Request $request, $id)
    {
        $manutencao = Manutencao::findOrFail($id);

        $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'descricao' => 'required|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'tipo' => 'required|in:preventiva,corretiva',
            'valor' => 'nullable|numeric',
            'oficina' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $manutencao->update($request->all());

        return redirect()->route('admin.manutencoes.index')->with('success', 'Manutenção atualizada com sucesso!');
    }

    // ADMIN: Remove manutenção
    public function destroy($id)
    {
        $manutencao = Manutencao::findOrFail($id);
        $manutencao->delete();

        return redirect()->route('admin.manutencoes.index')->with('success', 'Manutenção excluída com sucesso!');
    }

    // P4: Lista manutenções da OPM do usuário
    public function minhasManutencoes()
    {
        $manutencoes = Manutencao::whereHas('veiculo', function ($query) {
            $query->where('opm_id', auth()->user()->opm_id);
        })->with('veiculo')->get();

        return view('p4.manutencoes.index', compact('manutencoes'));
    }

    // P4: Formulário restrito de edição
    public function editarRestrito($id)
    {
        $manutencao = Manutencao::findOrFail($id);

        if ($manutencao->veiculo->opm_id !== auth()->user()->opm_id) {
            abort(403);
        }

        return view('p4.manutencoes.edit', compact('manutencao'));
    }

    // P4: Atualização restrita
    public function atualizarRestrito(Request $request, $id)
    {
        $manutencao = Manutencao::findOrFail($id);

        if ($manutencao->veiculo->opm_id !== auth()->user()->opm_id) {
            abort(403);
        }

        $request->validate([
            'descricao' => 'required|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'tipo' => 'required|in:preventiva,corretiva',
            'valor' => 'nullable|numeric',
            'oficina' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $manutencao->update($request->all());

        return redirect()->route('p4.manutencoes.index')->with('success', 'Manutenção atualizada com sucesso!');
    }
}
