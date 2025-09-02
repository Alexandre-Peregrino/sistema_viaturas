<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use App\Models\Radio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VeiculoController extends Controller
{
    /**
     * LISTAGEM (view: admin.viaturas.index)
     * Mostra OPM, Município (via OPM) e permite exibir a lotação atual (coluna opcional).
     */
    public function index()
    {
        return view('admin.viaturas.index');
    }


    /**
     * RELATÓRIO (view: admin.relatorios.resultados.viaturas)
     * Filtros: usar_lotacao, mostrar_tempo, opm_id, tipos[], combustiveis[], tracoes[]
     */
    public function relatorio(Request $request)
    {
        $usarLotacao  = $request->boolean('usar_lotacao');
        $mostrarTempo = $request->boolean('mostrar_tempo');

        $query = Veiculo::query()->with('opm');

        if ($usarLotacao) {
            if ($request->filled('opm_id')) {
                $query->whereHas('lotacoes', function ($q) use ($request) {
                    $q->whereNull('data_saida')->where('opm_id', $request->opm_id);
                });
            }
            $query->with([
                'lotacoes' => function ($q) {
                    $q->whereNull('data_saida')->with('opm', 'municipio');
                }
            ]);
        } else {
            if ($request->filled('opm_id')) {
                $query->where('opm_id', $request->opm_id);
            }
        }

        if ($request->filled('tipos'))        $query->whereIn('tipo_veiculo', (array) $request->tipos);
        if ($request->filled('combustiveis')) $query->whereIn('combustivel', (array) $request->combustiveis);
        if ($request->filled('tracoes'))      $query->whereIn('tracao', (array) $request->tracoes);

        $viaturas = $query
            ->withCount('manutencoes')
            ->orderBy('prefixo')
            ->paginate(50)
            ->withQueryString();

        return view('admin.relatorios.resultados.viaturas', [
            'viaturas'      => $viaturas,
            'titulo'        => $usarLotacao
                ? 'Relatório de Viaturas — por Lotação Atual'
                : 'Relatório de Viaturas — por Cadastro',
            'usarLotacao'   => $usarLotacao,
            'mostrarTempo'  => $mostrarTempo,
        ]);
    }

    /**
     * Aba: Viaturas por OPM (mostra viaturas atualmente lotadas na OPM).
     */
    public function porOpm(Request $request)
    {
        $opms = Opm::orderBy('sigla')->get(['id', 'sigla']);
        $opmId = (int) $request->input('opm_id');

        $veiculos = collect();

        if ($opmId) {
            $veiculos = Veiculo::whereHas('lotacoes', function ($q) use ($opmId) {
                $q->whereNull('data_saida')->where('opm_id', $opmId);
            })
                ->with([
                    'lotacoes' => fn($q) => $q->whereNull('data_saida')->with('opm', 'municipio'),
                ])
                ->withCount('manutencoes')
                ->orderBy('prefixo')
                ->paginate(50)
                ->withQueryString();
        }

        return view('admin.viaturas.por_opm', compact('opms', 'opmId', 'veiculos'));
    }

    /** CREATE */
    public function create()
    {
        $opms = Opm::with('municipio:id,nome')->orderBy('sigla')->get();

        // Apenas rádios não vinculados a nenhuma viatura e não inativos
        $radiosDisponiveis = Radio::disponiveis()
            ->orderBy('numero_serie')
            ->get(['numero_serie', 'marca', 'modelo']);

        // Para compatibilidade com views antigas/novas
        $radios = $radiosDisponiveis;

        return view('admin.viaturas.create', compact('opms', 'radios', 'radiosDisponiveis'));
    }

    /** STORE */
    public function store(Request $request)
    {
        $currentYearPlusOne = now()->year + 1;

        $validated = $request->validate([
            'prefixo'           => ['required', 'string', 'max:255'],
            'placa'             => [
                'required',
                'string',
                'max:7',
                // ABC1234 (antigo) OU ABC1D23 (Mercosul)
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                'unique:veiculos,placa',
            ],
            'marca_modelo'      => ['required', 'string', 'max:255'],
            'tipo_veiculo'      => ['required', 'string', 'max:255'],
            'opm_id'            => ['required', 'exists:opms,id'],
            'cor'               => ['nullable', 'string', 'max:255'],
            'ano_fabricacao'    => ['required', 'integer', 'between:1900,' . $currentYearPlusOne],
            'ano_modelo'        => ['nullable', 'integer', 'between:1900,' . $currentYearPlusOne],
            'chassi'            => ['required', 'string', 'size:17', 'alpha_num', 'unique:veiculos,chassi'],
            'renavam'           => ['required', 'regex:/^\d{9,11}$/', 'unique:veiculos,renavam'],
            'combustivel'       => ['required', 'string', 'max:255'],
            'capacidade_tanque' => ['nullable', 'numeric', 'min:0'],
            'quilometragem'     => ['nullable', 'numeric', 'min:0'],
            'observacao'        => ['nullable', 'string'],
            'cidade'            => ['required', 'string', 'max:255'],
            'situacao_carga'    => ['required', 'string', 'max:255'],
            'emprego'           => ['required', 'string', 'max:255'],
            'tipo_uso'          => ['required', 'string', 'max:255'],
            'layout'            => ['required', 'string', 'max:255'],
            'tracao'            => ['required', 'string', 'max:255'],
            'area'              => ['required', 'string', 'max:255'],
            'categoria'         => ['required', 'string', 'max:255'],
            'aquisicao_dados'   => ['nullable', 'date'],
            'entrega_dados_opm' => ['nullable', 'date'],
            'numero_serie_radio' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('radios', 'numero_serie'),
                Rule::unique('veiculos', 'numero_serie_radio'),
            ],
            'status'            => ['required', 'string', 'in:Ativo,Em Processo de Descarga'],
        ], [
            'placa.unique'      => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'       => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'     => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique'    => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'     => 'RENAVAM deve conter entre 9 e 11 dígitos.',
        ]);

        // Normalizações
        $validated['placa']  = strtoupper($validated['placa']);
        $validated['chassi'] = strtoupper($validated['chassi']);
        if (empty($validated['numero_serie_radio'])) {
            $validated['numero_serie_radio'] = null;
        }

        Veiculo::create($validated);

        return redirect()
            ->route('admin.viaturas.index')
            ->with('success', 'Viatura cadastrada com sucesso!');
    }

    /** EDIT */
    public function edit($id)
    {
        $viatura = Veiculo::with('opm')->findOrFail($id);
        $opms = Opm::with('municipio:id,nome')->orderBy('sigla')->get();

        // Rádios disponíveis + inclui o rádio já vinculado (para não sumir do select)
        $radiosDisponiveis = Radio::disponiveis()
            ->orderBy('numero_serie')
            ->get(['numero_serie', 'marca', 'modelo']);

        if ($viatura->numero_serie_radio) {
            $atual = Radio::where('numero_serie', $viatura->numero_serie_radio)
                ->first(['numero_serie', 'marca', 'modelo']);
            if ($atual && $radiosDisponiveis->where('numero_serie', $atual->numero_serie)->isEmpty()) {
                $radiosDisponiveis->push($atual);
                $radiosDisponiveis = $radiosDisponiveis->sortBy('numero_serie')->values();
            }
        }

        // Compatibilidade com views
        $radios = $radiosDisponiveis;

        return view('admin.viaturas.edit', compact('viatura', 'opms', 'radios', 'radiosDisponiveis'));
    }

    /** UPDATE */
    public function update(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);
        $currentYearPlusOne = now()->year + 1;

        $validated = $request->validate([
            'prefixo'           => ['required', 'string', 'max:255'],
            'placa'             => [
                'required',
                'string',
                'max:7',
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                Rule::unique('veiculos', 'placa')->ignore($viatura->id),
            ],
            'marca_modelo'      => ['required', 'string', 'max:255'],
            'tipo_veiculo'      => ['required', 'string', 'max:255'],
            'opm_id'            => ['required', 'exists:opms,id'],
            'cor'               => ['nullable', 'string', 'max:255'],
            'ano_fabricacao'    => ['required', 'integer', 'between:1900,' . $currentYearPlusOne],
            'ano_modelo'        => ['nullable', 'integer', 'between:1900,' . $currentYearPlusOne],
            'chassi'            => ['required', 'string', 'size:17', 'alpha_num', Rule::unique('veiculos', 'chassi')->ignore($viatura->id)],
            'renavam'           => ['required', 'regex:/^\d{9,11}$/', Rule::unique('veiculos', 'renavam')->ignore($viatura->id)],
            'combustivel'       => ['required', 'string', 'max:255'],
            'capacidade_tanque' => ['nullable', 'numeric', 'min:0'],
            'quilometragem'     => ['nullable', 'numeric', 'min:0'],
            'observacao'        => ['nullable', 'string'],
            'cidade'            => ['required', 'string', 'max:255'],
            'situacao_carga'    => ['required', 'string', 'max:255'],
            'emprego'           => ['required', 'string', 'max:255'],
            'tipo_uso'          => ['required', 'string', 'max:255'],
            'layout'            => ['required', 'string', 'max:255'],
            'tracao'            => ['required', 'string', 'max:255'],
            'area'              => ['required', 'string', 'max:255'],
            'categoria'         => ['required', 'string', 'max:255'],
            'aquisicao_dados'   => ['nullable', 'date'],
            'entrega_dados_opm' => ['nullable', 'date'],
            'numero_serie_radio' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('radios', 'numero_serie'),
                Rule::unique('veiculos', 'numero_serie_radio')->ignore($viatura->id),
            ],
            'status'            => ['required', 'string', 'in:Ativo,Em Processo de Descarga'],
        ], [
            'placa.unique'      => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'       => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'     => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique'    => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'     => 'RENAVAM deve conter entre 9 e 11 dígitos.',
        ]);

        // Normalizações
        $validated['placa']  = strtoupper($validated['placa']);
        $validated['chassi'] = strtoupper($validated['chassi']);
        if (empty($validated['numero_serie_radio'])) {
            $validated['numero_serie_radio'] = null;
        }

        $viatura->update($validated);

        return redirect()->route('admin.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }

    /** DESTROY */
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
            'quilometragem' => ['nullable', 'numeric', 'min:0'],
            'observacao'    => ['nullable', 'string'],
            'status'        => ['required', 'string', 'in:Ativo,Em Processo de Descarga'],
            'situacao_carga' => ['nullable', 'string', 'max:255'],
        ]);

        $viatura->update($request->only(['quilometragem', 'observacao', 'status', 'situacao_carga']));

        return redirect()->route('p4.viaturas.index')->with('success', 'Viatura atualizada com sucesso!');
    }
}
