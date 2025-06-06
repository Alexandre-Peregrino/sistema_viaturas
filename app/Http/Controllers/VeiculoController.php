<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veiculo;
use App\Models\Opm;

class VeiculoController extends Controller
{
    // ADMIN - Lista todas as viaturas
    public function index()
    {
        $viaturas = Veiculo::with(['opm', 'marcaModelo'])->get();

        return view('admin.viaturas.index', compact('viaturas'));
    }

    // P4 - Lista viaturas da OPM do usuário
    public function minhasViaturas()
    {
        $opmId = auth()->user()->opm_id;

        $viaturas = Veiculo::where('opm_id', $opmId)->with('marcaModelo')->get();

        return view('p4.viaturas.index', compact('viaturas'));
    }

    // ADMIN - Formulário de criação
    public function create()
    {
        // Apenas exemplo básico — você pode carregar dados adicionais aqui
        return view('admin.viaturas.create');
    }

    // ADMIN - Armazena nova viatura
    public function store(Request $request)
    {
        Veiculo::create($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura criada com sucesso!');
    }

    // ADMIN - Formulário de edição
    public function edit($id)
    {
        $viatura = Veiculo::findOrFail($id);

        return view('admin.viaturas.edit', compact('viatura'));
    }

    // ADMIN - Atualiza viatura
    public function update(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);
        $viatura->update($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }

    // ADMIN - Deleta viatura
    public function destroy($id)
    {
        $viatura = Veiculo::findOrFail($id);
        $viatura->delete();

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura excluída com sucesso!');
    }

    // P4 - Edita viatura restrito à OPM
    public function editarRestrito($id)
    {
        $viatura = Veiculo::where('id', $id)->where('opm_id', auth()->user()->opm_id)->firstOrFail();

        return view('p4.viaturas.edit', compact('viatura'));
    }

    // P4 - Atualiza viatura restrita
    public function atualizarRestrito(Request $request, $id)
    {
        $viatura = Veiculo::where('id', $id)->where('opm_id', auth()->user()->opm_id)->firstOrFail();
        $viatura->update($request->all());

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }
}
