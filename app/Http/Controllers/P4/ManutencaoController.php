<?php

namespace App\Http\Controllers\P4;

use App\Http\Controllers\Controller;
use App\Models\Manutencao;
use Illuminate\Http\Request;

class ManutencaoController extends Controller
{
    public function __construct()
    {
        // O grupo de rotas já restringe o papel; aqui garantimos autenticação.
        $this->middleware(['auth']);
    }

    /**
     * Lista APENAS as manutenções de veículos da OPM do usuário (P4).
     */
    public function index(Request $request)
    {
        $opmId = (int) auth()->user()->opm_id;

        // Autoriza coleção (Admin e P4 liberados). Policy: ManutencaoPolicy@viewAny
        $this->authorize('viewAny', Manutencao::class);

        $manutencoes = Manutencao::query()
            ->whereHas('veiculo', fn ($q) => $q->where('opm_id', $opmId))
            ->with(['veiculo:id,opm_id,prefixo,placa,marca_modelo,tipo_veiculo'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('p4.manutencoes.index', compact('manutencoes'));
    }

    /**
     * Detalhe da manutenção — se não pertencer à OPM do P4, retorna 404.
     * (Admin sempre pode; P4 só se veiculo->opm_id bater).
     */
    public function show($id)
    {
        $opmId = (int) auth()->user()->opm_id;

        $manutencao = Manutencao::query()
            ->whereKey($id)
            ->whereHas('veiculo', fn ($q) => $q->where('opm_id', $opmId))
            ->with(['veiculo:id,opm_id,prefixo,placa,marca_modelo,tipo_veiculo'])
            ->firstOrFail();

        $this->authorize('view', $manutencao);

        return view('p4.manutencoes.show', compact('manutencao'));
    }

    /**
     * Form de edição — se não pertencer à OPM do P4, retorna 404.
     */
    public function edit($id)
    {
        $opmId = (int) auth()->user()->opm_id;

        $manutencao = Manutencao::query()
            ->whereKey($id)
            ->whereHas('veiculo', fn ($q) => $q->where('opm_id', $opmId))
            ->with(['veiculo:id,opm_id,prefixo,placa,marca_modelo,tipo_veiculo'])
            ->firstOrFail();

        $this->authorize('update', $manutencao);

        return view('p4.manutencoes.edit', compact('manutencao'));
    }

    /**
     * Atualiza campos permitidos ao P4 — se não pertencer à OPM do P4, retorna 404.
     */
    public function update(Request $request, $id)
    {
        $opmId = (int) auth()->user()->opm_id;

        $manutencao = Manutencao::query()
            ->whereKey($id)
            ->whereHas('veiculo', fn ($q) => $q->where('opm_id', $opmId))
            ->firstOrFail();

        $this->authorize('update', $manutencao);

        $data = $request->validate([
            'status'      => 'required|string|max:50',
            'descricao'   => 'nullable|string|max:2000',
            'data_inicio' => 'nullable|date',
            'data_fim'    => 'nullable|date',
            'valor'       => 'nullable|numeric|min:0',
        ]);

        $manutencao->update($data);

        return redirect()
            ->route('p4.manutencoes.index')
            ->with('success', 'Manutenção atualizada.');
    }
}
