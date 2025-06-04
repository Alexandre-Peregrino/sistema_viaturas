<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VeiculoController extends Controller
{
    /**
     * Admin: Lista todas as viaturas de todas as OPMs.
     */
    public function index()
    {
        $veiculos = Veiculo::with('opm')->paginate(10);
        return view('admin.viaturas.index', compact('veiculos'));
    }

    /**
     * Admin: Exibe o formulário de criação de viatura.
     */
    public function create()
    {
        $opms = Opm::all();
        return view('admin.viaturas.create', compact('opms'));
    }

    /**
     * Admin: Armazena uma nova viatura.
     */
    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|unique:veiculos',
            'prefixo' => 'required|string',
            'opm_id' => 'required|exists:opms,id',
            // adicione outras validações necessárias
        ]);

        Veiculo::create($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura cadastrada com sucesso.');
    }

    /**
     * Admin: Formulário de edição completa.
     */
    public function edit($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $opms = Opm::all();
        return view('admin.viaturas.edit', compact('veiculo', 'opms'));
    }

    /**
     * Admin: Atualiza os dados da viatura.
     */
    public function update(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id);

        $request->validate([
            'placa' => 'required|string|unique:veiculos,placa,' . $veiculo->id,
            'prefixo' => 'required|string',
            'opm_id' => 'required|exists:opms,id',
            // outras validações
        ]);

        $veiculo->update($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso.');
    }

    /**
     * Admin: Remove a viatura.
     */
    public function destroy($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $veiculo->delete();

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura removida.');
    }

    /**
     * P4: Lista apenas viaturas da OPM do usuário logado.
     */
    public function minhasViaturas()
    {
        $user = Auth::user();
        $veiculos = Veiculo::where('opm_id', $user->opm_id)->paginate(10);
        return view('p4.viaturas.index', compact('veiculos'));
    }

    /**
     * P4: Formulário para edição restrita.
     */
    public function editarRestrito($id)
    {
        $user = Auth::user();
        $veiculo = Veiculo::where('id', $id)->where('opm_id', $user->opm_id)->firstOrFail();
        return view('p4.viaturas.edit', compact('veiculo'));
    }

    /**
     * P4: Atualiza apenas prefixo, trabalho e observação.
     */
    public function atualizarRestrito(Request $request, $id)
    {
        $user = Auth::user();
        $veiculo = Veiculo::where('id', $id)->where('opm_id', $user->opm_id)->firstOrFail();

        $request->validate([
            'prefixo' => 'required|string',
            'emprego' => 'nullable|string',
            'observacao' => 'nullable|string',
        ]);

        $veiculo->update([
            'prefixo' => $request->prefixo,
            'emprego' => $request->emprego,
            'observacao' => $request->observacao,
        ]);

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada.');
    }
}