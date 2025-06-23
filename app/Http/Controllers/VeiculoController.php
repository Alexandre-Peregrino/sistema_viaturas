<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use App\Models\Radio;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    /**
     * Lista de viaturas.
     */
    public function index()
    {
        $viaturas = Veiculo::with('opm')->get();
        return view('admin.viaturas.index', compact('viaturas'));
    }

    /**
     * Formulário de cadastro.
     */
    public function create()
    {
        $opms = Opm::all();
        $radios = Radio::all();
        return view('admin.viaturas.create', compact('opms', 'radios'));
    }

    /**
     * Armazena uma nova viatura.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prefixo' => 'required|string|max:255',
            'placa' => [
                'required',
                'string',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
                'unique:veiculos,placa',
                'max:255'
            ],
            'marca_modelo' => 'required|string|max:255',
            'tipo_veiculo' => 'required|string|max:255',
            'opm_id' => 'required|exists:opms,id',
            'cor' => 'nullable|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'ano_modelo' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'chassi' => [
                'required',
                'string',
                'size:17',
                'unique:veiculos,chassi',
            ],
            'renavam' => 'required|string|max:255|unique:veiculos,renavam',
            'combustivel' => 'required|string|max:255',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'cidade' => 'required|string|max:255',
            'situacao_carga' => 'required|string|max:255',
            'emprego' => 'required|string|max:255',
            'tipo_uso' => 'required|string|max:255',
            'layout' => 'required|string|max:255',
            'tracao' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'aquisicao_dados' => 'nullable|date',
            'entrega_dados_opm' => 'nullable|date',
            'numero_serie_radio' => 'nullable|string|exists:radios,numero_serie',
            'status' => 'required|string|in:Ativo,Em Processo de Descarga',
        ], [
            'placa.unique' => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex' => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique' => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
        ]);

        Veiculo::create($request->except(['ativo', 'em_processo_descarga']));

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura cadastrada com sucesso!');
    }

    /**
     * Formulário de edição.
     */
    public function edit($id)
    {
        $viatura = Veiculo::with('opm')->findOrFail($id);
        $opms = Opm::all();
        $radios = Radio::all();
        return view('admin.viaturas.edit', compact('viatura', 'opms', 'radios'));
    }

    /**
     * Atualiza uma viatura.
     */
    public function update(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);

        $request->validate([
            'prefixo' => 'required|string|max:255',
            'placa' => [
                'required',
                'string',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
                'unique:veiculos,placa,' . $viatura->id,
                'max:255'
            ],
            'marca_modelo' => 'required|string|max:255',
            'tipo_veiculo' => 'required|string|max:255',
            'opm_id' => 'required|exists:opms,id',
            'cor' => 'nullable|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'ano_modelo' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'chassi' => [
                'required',
                'string',
                'size:17',
                'unique:veiculos,chassi,' . $viatura->id,
            ],
            'renavam' => 'required|string|max:255|unique:veiculos,renavam,' . $viatura->id,
            'combustivel' => 'required|string|max:255',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'cidade' => 'required|string|max:255',
            'situacao_carga' => 'required|string|max:255',
            'emprego' => 'required|string|max:255',
            'tipo_uso' => 'required|string|max:255',
            'layout' => 'required|string|max:255',
            'tracao' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'aquisicao_dados' => 'nullable|date',
            'entrega_dados_opm' => 'nullable|date',
            'numero_serie_radio' => 'nullable|string|exists:radios,numero_serie',
            'status' => 'required|string|in:Ativo,Em Processo de Descarga',
        ], [
            'placa.unique' => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex' => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique' => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
        ]);

        $viatura->update($request->except(['ativo', 'em_processo_descarga']));

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }

    /**
     * Remove uma viatura.
     */
    public function destroy($id)
    {
        $viatura = Veiculo::findOrFail($id);
        $viatura->delete();

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura excluída com sucesso!');
    }

    /**
     * Edição restrita (P4).
     */
    public function editarRestrito($id)
    {
        $viatura = Veiculo::with('opm')->findOrFail($id);
        $opms = Opm::all();
        return view('p4.viaturas.editar', compact('viatura', 'opms'));
    }

    public function atualizarRestrito(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);

        $request->validate([
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|string|in:Ativo,Em Processo de Descarga',
            'situacao_carga' => 'nullable|string|max:255',
        ]);

        $viatura->update($request->only(['quilometragem', 'observacao', 'status', 'situacao_carga']));

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }
}
