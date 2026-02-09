<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use App\Models\Radio;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class VeiculoController extends Controller
{
    public function index()
    {
        return view('admin.viaturas.index');
    }

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

    public function porOpm(Request $request)
    {
        $opms  = Opm::orderBy('sigla')->get(['id', 'sigla']);
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

    private function getFormOptions(): array
    {
        $opmIds = Veiculo::query()
            ->whereNotNull('opm_id')
            ->distinct()
            ->pluck('opm_id');

        $opms = Opm::query()
            ->whereIn('id', $opmIds)
            ->orderBy('sigla')
            ->get(['id', 'sigla', 'nome']);

        $cidades = Veiculo::query()
            ->select('cidade')
            ->whereNotNull('cidade')
            ->whereRaw("TRIM(cidade) <> ''")
            ->distinct()
            ->orderBy('cidade')
            ->pluck('cidade');

        $areas = Veiculo::query()
            ->select('area')
            ->whereNotNull('area')
            ->whereRaw("TRIM(area) <> ''")
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $municipioIds = Veiculo::query()
            ->select('municipio_id')
            ->whereNotNull('municipio_id')
            ->distinct()
            ->pluck('municipio_id');

        $municipios = Municipio::query()
            ->whereIn('id', $municipioIds)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return compact('opms', 'cidades', 'areas', 'municipios');
    }

    public function create()
    {
        $radiosDisponiveis = Radio::disponiveis()
            ->orderBy('numero_serie')
            ->get(['numero_serie', 'marca', 'modelo']);

        $opts = $this->getFormOptions();

        return view('admin.viaturas.create', [
            'opms'              => $opts['opms'],
            'cidades'           => $opts['cidades'],
            'areas'             => $opts['areas'],
            'municipios'        => $opts['municipios'],
            'radios'            => $radiosDisponiveis,
            'radiosDisponiveis' => $radiosDisponiveis,
        ]);
    }

    private function normalizeRequest(Request $request): void
    {
        if ($request->has('placa')) {
            $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->input('placa')));
            $request->merge(['placa' => $placa]);
        }

        if ($request->has('prefixo') && is_string($request->input('prefixo'))) {
            $request->merge(['prefixo' => trim((string)$request->input('prefixo'))]);
        }

        if ($request->has('chassi')) {
            $chassi = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->input('chassi')));
            $request->merge(['chassi' => $chassi]);
        }

        if ($request->has('renavam')) {
            $renavam = preg_replace('/\D/', '', (string) $request->input('renavam'));
            $request->merge(['renavam' => $renavam]);
        }

        if ($request->has('numero_serie_radio')) {
            $v = trim((string)$request->input('numero_serie_radio'));
            $request->merge(['numero_serie_radio' => $v === '' ? null : $v]);
        }

        foreach ([
            'marca_modelo','cidade','area','tracao','categoria','emprego','layout',
            'situacao_carga','proprietario','contrato','processo_sei','observacao','status',
            'tipo_veiculo_outro','combustivel_outro'
        ] as $k) {
            if ($request->has($k) && is_string($request->input($k))) {
                $v = trim((string) $request->input($k));
                $request->merge([$k => $v === '' ? null : $v]);
            }
        }

        if ($request->has('municipio_id') && (string) $request->input('municipio_id') === '') {
            $request->merge(['municipio_id' => null]);
        }

        if ($request->has('dt_inicial_garantia') && (string)$request->input('dt_inicial_garantia') === '') {
            $request->merge(['dt_inicial_garantia' => null]);
        }

        if ($request->has('garantia_bateria_meses') && (string)$request->input('garantia_bateria_meses') === '') {
            $request->merge(['garantia_bateria_meses' => null]);
        }

        // CHECK-safe: não gravar "Outros" no campo com CHECK
        if ($request->input('tipo_veiculo') === 'Outros') {
            $request->merge(['tipo_veiculo' => null]);
        }

        if ($request->input('combustivel') === 'Outros') {
            $request->merge(['combustivel' => null]);
        }
    }

    private function applyGarantiaBateria(array &$validated): void
    {
        if (!empty($validated['dt_inicial_garantia']) && !empty($validated['garantia_bateria_meses'])) {
            $validated['dt_final_garantia'] = Carbon::parse($validated['dt_inicial_garantia'])
                ->addMonthsNoOverflow((int)$validated['garantia_bateria_meses'])
                ->format('Y-m-d');
        } else {
            $validated['dt_final_garantia'] = null;
            $validated['garantia_bateria_meses'] = null;
        }
    }

    public function store(Request $request)
    {
        // CAPTURA antes de normalizar (senão "Outros" vira null)
        $tipoSelecionado = $request->input('tipo_veiculo');
        $combSelecionado = $request->input('combustivel');

        $this->normalizeRequest($request);

        $currentYearPlusOne = now()->year + 1;

        $statusOptions = [
            'Ativo','Baixado','Em Proc de descarga','Descarregado','Entregue a COPAT','Devolvido a locadora',
        ];

        $tipoOptions = [
            'SUV','Pickup','Moto','Sedan','Hatch','Van','Caminhonete','Camioneta','Ônibus','Micro-Ônibus','Caminhão','Utilitário','Reboque',
        ];

        $combOptions = [
            'Gasolina','Diesel','Flex','Álcool','Elétrico','Híbrido','GNV',
        ];

        $validated = $request->validate([
            'marca_modelo'   => ['required', 'string', 'max:255'],
            'placa' => [
                'required','string','size:7','alpha_num',
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                Rule::unique('veiculos', 'placa'),
            ],
            'prefixo' => ['required', 'string', 'max:255', Rule::unique('veiculos', 'prefixo')],
            'cidade'  => ['required', 'string', 'max:255'],
            'area'    => ['required', 'string', 'max:255'],
            'opm_id'  => ['required', 'integer', Rule::exists('opms', 'id')],

            'chassi'  => ['required', 'string', 'size:17', 'alpha_num', Rule::unique('veiculos', 'chassi')],
            'renavam' => ['required', 'string', 'regex:/^\d{9,11}$/', Rule::unique('veiculos', 'renavam')],

            'status'  => ['required', 'string', Rule::in($statusOptions)],
            'layout'  => ['required', 'string', 'max:255'],

            'numero_serie_radio' => [
                'nullable','string','max:100',
                Rule::exists('radios', 'numero_serie'),
                Rule::unique('veiculos', 'numero_serie_radio'),
            ],

            'ano_fabricacao'     => ['nullable', 'integer', 'between:1900,' . $currentYearPlusOne],

            // IMPORTANTE: aqui NÃO tem required_with! é só nullable.
            'tipo_veiculo'       => ['nullable', 'string', 'max:255', Rule::in($tipoOptions)],
            'tipo_veiculo_outro' => ['nullable', 'string', 'max:255'],

            'combustivel'        => ['nullable', 'string', 'max:255', Rule::in($combOptions)],
            'combustivel_outro'  => ['nullable', 'string', 'max:255'],

            'tracao'             => ['nullable', 'string', 'max:255'],
            'categoria'          => ['nullable', 'string', 'max:255'],
            'emprego'            => ['nullable', 'string', 'max:255'],

            'aquisicao_dados'    => ['nullable', 'date'],
            'entrega_dados_opm'  => ['nullable', 'date'],

            'proprietario'       => ['nullable', 'string', 'max:255'],
            'contrato'           => ['nullable', 'string', 'max:255'],
            'processo_sei'       => ['nullable', 'string', 'max:255'],

            'dt_inicial_garantia'    => ['nullable', 'date'],
            'garantia_bateria_meses' => ['required_with:dt_inicial_garantia', 'integer', 'min:1', 'max:120'],

            'n_serie_bateria'    => ['nullable', 'string', 'max:80'],

            'situacao_carga'     => ['required', 'string', 'max:255'],

            'municipio_id'       => ['nullable', 'integer'],
            'observacao'         => ['nullable', 'string'],
        ], [
            'placa.unique'   => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'    => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'  => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'  => 'RENAVAM deve conter entre 9 e 11 dígitos.',
            'garantia_bateria_meses.required_with' => 'Informe o prazo de garantia (meses) quando a data inicial estiver preenchida.',
        ]);

        // Agora sim: valida "Outros" usando o valor escolhido ANTES da normalização
        if ($tipoSelecionado === 'Outros') {
            $request->validate([
                'tipo_veiculo_outro' => ['required', 'string', 'max:255'],
            ], [
                'tipo_veiculo_outro.required' => 'Informe qual é o tipo do veículo quando selecionar "Outros".',
            ]);
            $validated['tipo_veiculo'] = null; // CHECK-safe
            $validated['tipo_veiculo_outro'] = trim((string)$request->input('tipo_veiculo_outro'));
        } else {
            $validated['tipo_veiculo_outro'] = null;
        }

        if ($combSelecionado === 'Outros') {
            $request->validate([
                'combustivel_outro' => ['required', 'string', 'max:255'],
            ], [
                'combustivel_outro.required' => 'Informe qual é o combustível quando selecionar "Outros".',
            ]);
            $validated['combustivel'] = null; // CHECK-safe
            $validated['combustivel_outro'] = trim((string)$request->input('combustivel_outro'));
        } else {
            $validated['combustivel_outro'] = null;
        }

        $this->applyGarantiaBateria($validated);

        Veiculo::create($validated);

        return redirect()
            ->route('admin.viaturas.index')
            ->with('success', 'Viatura cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $veiculo = Veiculo::with('opm')->findOrFail($id);

        $radiosDisponiveis = Radio::disponiveis()
            ->orderBy('numero_serie')
            ->get(['numero_serie', 'marca', 'modelo']);

        if ($veiculo->numero_serie_radio) {
            $atual = Radio::where('numero_serie', $veiculo->numero_serie_radio)
                ->first(['numero_serie', 'marca', 'modelo']);

            if ($atual && $radiosDisponiveis->where('numero_serie', $atual->numero_serie)->isEmpty()) {
                $radiosDisponiveis->push($atual);
                $radiosDisponiveis = $radiosDisponiveis->sortBy('numero_serie')->values();
            }
        }

        $opts = $this->getFormOptions();

        return view('admin.viaturas.edit', [
            'veiculo'           => $veiculo,
            'opms'              => $opts['opms'],
            'cidades'           => $opts['cidades'],
            'areas'             => $opts['areas'],
            'municipios'        => $opts['municipios'],
            'radios'            => $radiosDisponiveis,
            'radiosDisponiveis' => $radiosDisponiveis,
        ]);
    }

    public function update(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id);

        // CAPTURA antes de normalizar
        $tipoSelecionado = $request->input('tipo_veiculo');
        $combSelecionado = $request->input('combustivel');

        $this->normalizeRequest($request);

        $currentYearPlusOne = now()->year + 1;

        $statusOptions = [
            'Ativo','Baixado','Em Proc de descarga','Descarregado','Entregue a COPAT','Devolvido a locadora',
        ];

        $tipoOptions = [
            'SUV','Pickup','Moto','Sedan','Hatch','Van','Caminhonete','Camioneta','Ônibus','Micro-Ônibus','Caminhão','Utilitário','Reboque',
        ];

        $combOptions = [
            'Gasolina','Diesel','Flex','Álcool','Elétrico','Híbrido','GNV',
        ];

        $validated = $request->validate([
            'marca_modelo'   => ['required', 'string', 'max:255'],
            'placa' => [
                'required','string','size:7','alpha_num',
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                Rule::unique('veiculos', 'placa')->ignore($veiculo->id),
            ],
            'prefixo' => ['required', 'string', 'max:255', Rule::unique('veiculos', 'prefixo')->ignore($veiculo->id)],
            'cidade'  => ['required', 'string', 'max:255'],
            'area'    => ['required', 'string', 'max:255'],
            'opm_id'  => ['required', 'integer', Rule::exists('opms', 'id')],

            'chassi'  => ['required', 'string', 'size:17', 'alpha_num', Rule::unique('veiculos', 'chassi')->ignore($veiculo->id)],
            'renavam' => ['required', 'string', 'regex:/^\d{9,11}$/', Rule::unique('veiculos', 'renavam')->ignore($veiculo->id)],

            'status'  => ['required', 'string', Rule::in($statusOptions)],
            'layout'  => ['required', 'string', 'max:255'],

            'numero_serie_radio' => [
                'nullable','string','max:100',
                Rule::exists('radios', 'numero_serie'),
                Rule::unique('veiculos', 'numero_serie_radio')->ignore($veiculo->id),
            ],

            'ano_fabricacao'     => ['nullable', 'integer', 'between:1900,' . $currentYearPlusOne],

            'tipo_veiculo'       => ['nullable', 'string', 'max:255', Rule::in($tipoOptions)],
            'tipo_veiculo_outro' => ['nullable', 'string', 'max:255'],

            'combustivel'        => ['nullable', 'string', 'max:255', Rule::in($combOptions)],
            'combustivel_outro'  => ['nullable', 'string', 'max:255'],

            'tracao'             => ['nullable', 'string', 'max:255'],
            'categoria'          => ['nullable', 'string', 'max:255'],
            'emprego'            => ['nullable', 'string', 'max:255'],

            'aquisicao_dados'    => ['nullable', 'date'],
            'entrega_dados_opm'  => ['nullable', 'date'],

            'proprietario'       => ['nullable', 'string', 'max:255'],
            'contrato'           => ['nullable', 'string', 'max:255'],
            'processo_sei'       => ['nullable', 'string', 'max:255'],

            'dt_inicial_garantia'    => ['nullable', 'date'],
            'garantia_bateria_meses' => ['required_with:dt_inicial_garantia', 'integer', 'min:1', 'max:120'],

            'n_serie_bateria'    => ['nullable', 'string', 'max:80'],

            'situacao_carga'     => ['required', 'string', 'max:255'],

            'municipio_id'       => ['nullable', 'integer'],
            'observacao'         => ['nullable', 'string'],
        ], [
            'placa.unique'   => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'    => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'  => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'  => 'RENAVAM deve conter entre 9 e 11 dígitos.',
            'garantia_bateria_meses.required_with' => 'Informe o prazo de garantia (meses) quando a data inicial estiver preenchida.',
        ]);

        if ($tipoSelecionado === 'Outros') {
            $request->validate([
                'tipo_veiculo_outro' => ['required', 'string', 'max:255'],
            ], [
                'tipo_veiculo_outro.required' => 'Informe qual é o tipo do veículo quando selecionar "Outros".',
            ]);
            $validated['tipo_veiculo'] = null;
            $validated['tipo_veiculo_outro'] = trim((string)$request->input('tipo_veiculo_outro'));
        } else {
            $validated['tipo_veiculo_outro'] = null;
        }

        if ($combSelecionado === 'Outros') {
            $request->validate([
                'combustivel_outro' => ['required', 'string', 'max:255'],
            ], [
                'combustivel_outro.required' => 'Informe qual é o combustível quando selecionar "Outros".',
            ]);
            $validated['combustivel'] = null;
            $validated['combustivel_outro'] = trim((string)$request->input('combustivel_outro'));
        } else {
            $validated['combustivel_outro'] = null;
        }

        $this->applyGarantiaBateria($validated);

        $veiculo->update($validated);

        return redirect()
            ->route('admin.viaturas.index')
            ->with('success', 'Viatura atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        $veiculo->delete();

        return redirect()
            ->route('admin.viaturas.index')
            ->with('success', 'Viatura excluída com sucesso!');
    }

    public function editarRestrito($id)
    {
        $viatura = Veiculo::with('opm')->findOrFail($id);
        $opms = Opm::all();

        return view('p4.viaturas.editar', compact('viatura', 'opms'));
    }

    public function atualizarRestrito(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);

        $this->normalizeRequest($request);

        $statusOptions = [
            'Ativo','Baixado','Em Proc de descarga','Descarregado','Entregue a COPAT','Devolvido a locadora',
        ];

        $validated = $request->validate([
            'status'         => ['required', 'string', Rule::in($statusOptions)],
            'situacao_carga' => ['nullable', 'string', 'max:255'],
            'observacao'     => ['nullable', 'string'],
        ]);

        $viatura->update($validated);

        return redirect()
            ->route('p4.viaturas.index')
            ->with('success', 'Viatura atualizada com sucesso!');
    }

    public function probeDb(Request $request)
    {
        try {
            $validated = $request->validate([
                'placa' => ['required', 'string', 'max:12'],
            ]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)($validated['placa'] ?? '')));

        $veiculo = Veiculo::query()
            ->whereRaw('UPPER(placa) = ?', [$placa])
            ->first();

        if (!$veiculo) {
            return back()
                ->withErrors(['placa' => 'Placa não encontrada no banco local.'])
                ->withInput();
        }

        return redirect()
            ->route('admin.viaturas.edit', $veiculo->id)
            ->with('status', 'Abrindo para edição...');
    }
}
