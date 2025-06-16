<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use App\Models\Radio; // Adicionado para o campo radio
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Exibe uma listagem de viaturas.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Eager load apenas a relação 'opm'
        $viaturas = Veiculo::with('opm')->get();
        return view('admin.viaturas.index', compact('viaturas'));
    }

    /**
     * Show the form for creating a new resource.
     * Exibe o formulário para cadastrar uma nova viatura.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $opms = Opm::all(); // Necessário para o dropdown de OPM
        $radios = Radio::all(); // Necessário para o dropdown de Rádio
        return view('admin.viaturas.create', compact('opms', 'radios'));
    }

    /**
     * Store a newly created resource in storage.
     * Armazena uma nova viatura no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'prefixo' => 'required|string|max:255',
            'placa' => 'required|string|unique:veiculos,placa|max:255',
            'marca_modelo' => 'required|string|max:255', // CORRIGIDO: Valida como 'marca_modelo'
            'tipo_veiculo' => 'required|string|max:255', // Valida como string
            'opm_id' => 'required|exists:opms,id',
            'cor' => 'nullable|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'ano_modelo' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'chassi' => 'required|string|max:255|unique:veiculos,chassi',
            'renavam' => 'required|string|max:255|unique:veiculos,renavam',
            'combustivel' => 'required|string|max:255',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|string|max:255',
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
            'ativo' => 'boolean',
            'em_processo_descarga' => 'boolean',
        ]);

        Veiculo::create($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura cadastrada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     * Exibe o formulário para editar uma viatura existente.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Eager load apenas a relação 'opm'
        $viatura = Veiculo::with('opm')->findOrFail($id);
        $opms = Opm::all();
        $radios = Radio::all(); // Necessário para o dropdown de Rádio
        return view('admin.viaturas.edit', compact('viatura', 'opms', 'radios'));
    }

    /**
     * Update the specified resource in storage.
     * Atualiza uma viatura existente no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);

        $request->validate([
            'prefixo' => 'required|string|max:255',
            'placa' => 'required|string|unique:veiculos,placa,' . $viatura->id . '|max:255',
            'marca_modelo' => 'required|string|max:255', // CORRIGIDO: Valida como 'marca_modelo'
            'tipo_veiculo' => 'required|string|max:255', // Valida como string
            'opm_id' => 'required|exists:opms,id',
            'cor' => 'nullable|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'ano_modelo' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'chassi' => 'required|string|max:255|unique:veiculos,chassi,' . $viatura->id,
            'renavam' => 'required|string|max:255|unique:veiculos,renavam,' . $viatura->id,
            'combustivel' => 'required|string|max:255',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|string|max:255',
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
            'ativo' => 'boolean',
            'em_processo_descarga' => 'boolean',
        ]);

        $viatura->update($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     * Exclui uma viatura do banco de dados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $viatura = Veiculo::findOrFail($id);
        $viatura->delete();

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura excluída com sucesso!');
    }

    // Métodos para P4 (se existirem, ajuste também para eager loading se necessário)
    public function editarRestrito($id)
    {
        $viatura = Veiculo::with('opm')->findOrFail($id); // Eager load apenas 'opm'
        $opms = Opm::all();
        return view('p4.viaturas.editar', compact('viatura', 'opms'));
    }

    public function atualizarRestrito(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);
        $request->validate([
            'quilometragem' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|string|max:255',
            'situacao_carga' => 'nullable|string|max:255',
        ]);

        $viatura->update($request->only(['quilometragem', 'observacao', 'status', 'situacao_carga']));

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }
}
