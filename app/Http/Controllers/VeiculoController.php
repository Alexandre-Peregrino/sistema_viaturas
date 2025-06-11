<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use Illuminate\Http\Request;
use App\Models\Radio;
// Certifique-se de que quaisquer outros 'use' statements necessários estejam aqui, como para MarcaModelo ou TipoVeiculo, se você os usar em outros lugares do controlador.

class VeiculoController extends Controller
{
    // Lista viaturas (admin e p4)
    public function index()
    {
        $usuario = auth()->user();

        if ($usuario->isAdmin()) {
            $veiculos = Veiculo::with(['opm'])->get();
            return view('admin.viaturas.index', compact('veiculos'));
        } elseif ($usuario->isP4()) {
            $veiculos = Veiculo::with(['opm'])
                ->where('opm_id', $usuario->opm_id)
                ->get();
            return view('p4.viaturas.index', compact('veiculos'));
        } else {
            abort(403, 'Acesso não autorizado');
        }
    }

    // Formulário de criação (apenas admin)
    public function create()
    {
        $opms = Opm::all();
        $radios = Radio::all();
        return view('admin.viaturas.create', compact('opms', 'radios'));
    }


    // Armazena nova viatura (apenas admin)
    public function store(Request $request)
    {
        $request->validate([
            'prefixo' => 'nullable|string|unique:veiculos,prefixo',
            'placa' => 'required|string|unique:veiculos,placa|max:10',
            'marca_modelo' => 'required|string|max:255',
            'tipo_veiculo' => 'required|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'combustivel' => 'required|string|max:255',
            'chassi' => 'required|string|unique:veiculos,chassi|max:255',
            'renavam' => 'required|string|unique:veiculos,renavam|max:20',
            'opm_id' => 'required|exists:opms,id',
            'situacao_carga' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
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
            'observacao' => 'nullable|string',
        ]);

        Veiculo::create($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura cadastrada com sucesso.');
    }

    // Formulário de edição (apenas admin)
    public function edit($id)
    {
        $viatura = Veiculo::findOrFail($id); // Variável é $viatura
        $opms = Opm::all();
        $radios = Radio::all();
        // CORRIGIDO AQUI: Passando $viatura no compact
        return view('admin.viaturas.edit', compact('viatura', 'opms', 'radios'));
    }

    // Atualiza dados da viatura (apenas admin)
    public function update(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id);

        $request->validate([
            'prefixo' => 'nullable|string|unique:veiculos,prefixo,' . $veiculo->id,
            'placa' => 'required|string|unique:veiculos,placa,' . $veiculo->id . '|max:10',
            'marca_modelo' => 'required|string|max:255',
            'tipo_veiculo' => 'required|string|max:255',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'combustivel' => 'required|string|max:255',
            'chassi' => 'required|string|unique:veiculos,chassi,' . $veiculo->id . '|max:255',
            'renavam' => 'required|string|unique:veiculos,renavam,' . $veiculo->id . '|max:20',
            'opm_id' => 'required|exists:opms,id',
            'situacao_carga' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
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
            'observacao' => 'nullable|string',
        ]);

        $veiculo->update($request->all());

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso.');
    }

    // Exclui uma viatura (apenas admin)
    public function destroy($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $veiculo->delete();

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura excluída com sucesso.');
    }

    // Métodos para P4
    public function editarRestrito($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        // Pode ser necessário passar $opms e $radios aqui se o formulário P4 também precisar
        return view('p4.viaturas.edit', compact('veiculo'));
    }

    public function atualizarRestrito(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        // Validação mais restrita para P4, talvez apenas alguns campos
        $request->validate([
            'situacao_carga' => 'required|string|max:255',
            'observacao' => 'nullable|string',
        ]);

        $veiculo->update($request->all());

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada (P4) com sucesso.');
    }
}
