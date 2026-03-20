<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Opm;
use App\Models\Usuario;
use App\Models\Radio;
use App\Models\Municipio;
use App\Models\VeiculoLotacao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

        // ✅ Regra nova: sempre carregar lotação atual (fonte da verdade)
        $query = Veiculo::query()->with(['lotacaoAtual.opm', 'lotacaoAtual.municipio']);

        // ✅ Filtro por OPM: sempre via lotação atual (não via veiculos.opm_id)
        if ($request->filled('opm_id')) {
            $query->whereHas('lotacaoAtual', function ($q) use ($request) {
                $q->where('opm_id', $request->opm_id);
            });
        }

        // (mantém filtros existentes)
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
                ? 'Relatório de Viaturas — por Lotação Atual (oficial)'
                : 'Relatório de Viaturas — por Lotação Atual (oficial)', // ✅ agora é sempre oficial
            'usarLotacao'   => $usarLotacao,
            'mostrarTempo'  => $mostrarTempo,
        ]);
    }

    public function porOpm(Request $request)
    {
        // ✅ Lista de OPMs por ordem
        $opms  = Opm::orderBy('sigla')->get(['id', 'sigla']);
        $opmId = (int) $request->input('opm_id');

        $veiculos = collect();

        if ($opmId) {
            // ✅ Regra nova: por OPM usando lotacaoAtual
            $veiculos = Veiculo::whereHas('lotacaoAtual', fn($q) => $q->where('opm_id', $opmId))
                ->with(['lotacaoAtual.opm', 'lotacaoAtual.municipio'])
                ->withCount('manutencoes')
                ->orderBy('prefixo')
                ->paginate(50)
                ->withQueryString();
        }

        return view('admin.viaturas.por_opm', compact('opms', 'opmId', 'veiculos'));
    }

    private function getFormOptions(): array
    {
        // ✅ Regra nova: OPMs “relevantes” vêm das lotações abertas (fonte oficial)
        $opmIds = VeiculoLotacao::query()
            ->whereNull('data_saida')
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

        foreach (
            [
                'marca_modelo',
                'cidade',
                'area',
                'tracao',
                'categoria',
                'emprego',
                'layout',
                'situacao_carga',
                'proprietario',
                'contrato',
                'processo_sei',
                'observacao',
                'status',
                'tipo_veiculo_outro',
                'combustivel_outro'
            ] as $k
        ) {
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

    /**
     * ✅ Resolve usuario_id de forma robusta:
     * - Se Auth::user() existir e tiver id numérico => usa.
     * - Senão, tenta interpretar Auth::id() como CPF e buscar usuarios.id.
     */
    private function resolveUsuarioId(): ?int
    {
        $u = Auth::user();
        if ($u && isset($u->id) && is_numeric($u->id)) {
            return (int) $u->id;
        }

        $authId = Auth::id(); // pode ser CPF no seu projeto
        if ($authId) {
            $cpf = (string) $authId;
            $id = Usuario::where('cpf', $cpf)->value('id');
            return $id ? (int)$id : null;
        }

        return null;
    }

    public function store(Request $request)
    {
        // CAPTURA antes de normalizar (senão "Outros" vira null)
        $tipoSelecionado = $request->input('tipo_veiculo');
        $combSelecionado = $request->input('combustivel');

        $this->normalizeRequest($request);

        $currentYearPlusOne = now()->year + 1;

        $statusOptions = [
            'Ativo',
            'Baixado',
            'Em Proc de descarga',
            'Descarregado',
            'Entregue a COPAT',
            'Devolvido a locadora',
        ];

        $tipoOptions = [
            'SUV',
            'Pickup',
            'Moto',
            'Sedan',
            'Hatch',
            'Van',
            'Caminhonete',
            'Camioneta',
            'Ônibus',
            'Micro-Ônibus',
            'Caminhão',
            'Utilitário',
            'Reboque',
        ];

        $combOptions = [
            'Gasolina',
            'Diesel',
            'Flex',
            'Álcool',
            'Elétrico',
            'Híbrido',
            'GNV',
        ];

        $request->merge([
            'status' => $request->input('status') !== '' ? $request->input('status') : null,
            'garantia_bateria_meses' => $request->input('garantia_bateria_meses') !== '' ? $request->input('garantia_bateria_meses') : null,
        ]);

        $validated = $request->validate([
            'marca_modelo'   => ['required', 'string', 'max:255'],
            'placa' => [
                'required',
                'string',
                'size:7',
                'alpha_num',
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                Rule::unique('veiculos', 'placa'),
            ],
            'prefixo' => ['required', 'string', 'max:255', Rule::unique('veiculos', 'prefixo')],
            'cidade'  => ['nullable', 'string', 'max:255'],
            'area'    => ['required', 'string', 'max:255'],
            'opm_id'  => ['required', 'integer', Rule::exists('opms', 'id')],

            'chassi'  => ['required', 'string', 'size:17', 'alpha_num', Rule::unique('veiculos', 'chassi')],
            'renavam' => ['required', 'string', 'regex:/^\d{9,11}$/', Rule::unique('veiculos', 'renavam')],

            'status'  => ['nullable', 'string', Rule::in($statusOptions)],
            'layout'  => ['required', 'string', 'max:255'],

            'numero_serie_radio' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('radios', 'numero_serie'),
                Rule::unique('veiculos', 'numero_serie_radio'),
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
            'garantia_bateria_meses' => ['nullable', 'integer', 'min:1', 'max:120'],
            'n_serie_bateria'    => ['nullable', 'string', 'max:80'],

            'situacao_carga'     => ['required', 'string', 'max:255'],

            'municipio_id'       => ['nullable', 'integer', Rule::exists('municipios', 'id')],
            'observacao'         => ['nullable', 'string'],
        ], [
            'placa.unique'   => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'    => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'  => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'  => 'RENAVAM deve conter entre 9 e 11 dígitos.',
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

        // ✅ cidade obrigatória no banco: deriva do município selecionado
        if (!empty($validated['municipio_id'])) {
            $municipio = Municipio::query()->find($validated['municipio_id']);
            $validated['cidade'] = $municipio?->nome;
        }

        if (empty($validated['cidade'])) {
            return back()
                ->withInput()
                ->withErrors(['municipio_id' => 'Não foi possível preencher a cidade a partir do município selecionado.']);
        }

        $veiculo = Veiculo::create($validated);

        $usuarioId = $this->resolveUsuarioId();

        // ✅ cria lotação inicial oficial (aberta)
        VeiculoLotacao::create([
            'veiculo_id'   => $veiculo->id,
            'opm_id'       => (int) $validated['opm_id'],
            'municipio_id' => $validated['municipio_id'] ?? null,
            'data_entrada' => $validated['entrega_dados_opm'] ?? $validated['aquisicao_dados'] ?? now()->toDateString(),
            'data_saida'   => null,
            'motivo'       => 'Cadastro inicial',
            'observacao'   => null,
            'usuario_id'   => $usuarioId,
        ]);

        return redirect()
            ->route('admin.viaturas.index')
            ->with('success', 'Viatura cadastrada com sucesso!');
    }

    public function edit($id)
    {
        // ✅ traz lotação atual + histórico completo (com OPM/Município/Usuário)
        $veiculo = Veiculo::with([
            'opm',
            'lotacaoAtual.opm',
            'lotacaoAtual.municipio',
            'lotacoes' => function ($q) {
                $q->with(['opm', 'municipio', 'usuario'])
                    ->orderBy('data_entrada', 'desc')
                    ->orderBy('id', 'desc');
            },
        ])->findOrFail($id);

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

        $tipoSelecionado = $request->input('tipo_veiculo');
        $combSelecionado = $request->input('combustivel');

        $this->normalizeRequest($request);

        $currentYearPlusOne = now()->year + 1;

        $statusOptions = [
            'Ativo',
            'Baixado',
            'Em Proc de descarga',
            'Descarregado',
            'Entregue a COPAT',
            'Devolvido a locadora',
        ];

        $tipoOptions = [
            'SUV',
            'Pickup',
            'Moto',
            'Sedan',
            'Hatch',
            'Van',
            'Caminhonete',
            'Camioneta',
            'Ônibus',
            'Micro-Ônibus',
            'Caminhão',
            'Utilitário',
            'Reboque',
        ];

        $combOptions = [
            'Gasolina',
            'Diesel',
            'Flex',
            'Álcool',
            'Elétrico',
            'Híbrido',
            'GNV',
        ];

        $validated = $request->validate([
            'marca_modelo'   => ['required', 'string', 'max:255'],
            'placa' => [
                'required',
                'string',
                'size:7',
                'alpha_num',
                'regex:/^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/',
                Rule::unique('veiculos', 'placa')->ignore($veiculo->id),
            ],
            'prefixo' => ['required', 'string', 'max:255', Rule::unique('veiculos', 'prefixo')->ignore($veiculo->id)],
            'cidade'  => ['nullable', 'string', 'max:255'],
            'area'    => ['required', 'string', 'max:255'],
            'opm_id'  => ['required', 'integer', Rule::exists('opms', 'id')],

            'chassi'  => ['required', 'string', 'size:17', 'alpha_num', Rule::unique('veiculos', 'chassi')->ignore($veiculo->id)],
            'renavam' => ['required', 'string', 'regex:/^\d{9,11}$/', Rule::unique('veiculos', 'renavam')->ignore($veiculo->id)],

            'status'  => ['nullable', 'string', Rule::in($statusOptions)],
            'layout'  => ['required', 'string', 'max:255'],

            'numero_serie_radio' => [
                'nullable',
                'string',
                'max:100',
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
            'garantia_bateria_meses' => ['nullable', 'integer', 'min:1', 'max:120'],
            'n_serie_bateria'    => ['nullable', 'string', 'max:80'],

            'situacao_carga'     => ['required', 'string', 'max:255'],

            'municipio_id'       => ['nullable', 'integer', Rule::exists('municipios', 'id')],
            'observacao'         => ['nullable', 'string'],
        ], [
            'placa.unique'   => 'Esta placa já está cadastrada para outra viatura.',
            'placa.regex'    => 'A placa deve seguir o padrão válido (ABC1234 ou ABC1D23).',
            'chassi.unique'  => 'Este chassi já está cadastrado para outra viatura.',
            'renavam.unique' => 'Este Renavam já está cadastrado para outra viatura.',
            'renavam.regex'  => 'RENAVAM deve conter entre 9 e 11 dígitos.',
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
        // ✅ cidade obrigatória no banco: deriva do município selecionado
        if (!empty($validated['municipio_id'])) {
            $municipio = Municipio::query()->find($validated['municipio_id']);
            $validated['cidade'] = $municipio?->nome;
        }

        if (empty($validated['cidade'])) {
            return back()
                ->withInput()
                ->withErrors(['municipio_id' => 'Não foi possível preencher a cidade a partir do município selecionado.']);
        }

        // --- Detecta troca de OPM/Município para registrar movimentação oficial ---
        $opmNovo = (int) $validated['opm_id'];
        $munNovo = $validated['municipio_id'] ?? null;

        $lotacaoAtual = VeiculoLotacao::where('veiculo_id', $veiculo->id)
            ->whereNull('data_saida')
            ->latest('data_entrada')
            ->first();

        $opmAtual = $lotacaoAtual?->opm_id;
        $munAtual = $lotacaoAtual?->municipio_id;

        $mudouLotacao = ($lotacaoAtual === null)
            || ((int)$opmAtual !== (int)$opmNovo)
            || ((string)$munAtual !== (string)$munNovo);

        // Atualiza o veículo (cache + demais campos)
        $veiculo->update($validated);

        // ✅ Só mexe na tabela de movimentações se realmente mudou
        if ($mudouLotacao) {
            if ($lotacaoAtual) {
                $lotacaoAtual->update([
                    'data_saida' => now()->toDateString(),
                ]);
            }

            $usuarioId = $this->resolveUsuarioId();

            VeiculoLotacao::create([
                'veiculo_id'   => $veiculo->id,
                'opm_id'       => $opmNovo,
                'municipio_id' => $munNovo,
                'data_entrada' => now()->toDateString(),
                'data_saida'   => null,
                'motivo'       => 'Alteração de lotação (update)',
                'observacao'   => null,
                'usuario_id'   => $usuarioId,
            ]);
        }

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
        $viatura = Veiculo::with(['lotacaoAtual.opm', 'lotacaoAtual.municipio'])->findOrFail($id);
        $opms = Opm::all();

        return view('p4.viaturas.editar', compact('viatura', 'opms'));
    }

    public function atualizarRestrito(Request $request, $id)
    {
        $viatura = Veiculo::findOrFail($id);

        $this->normalizeRequest($request);

        $statusOptions = [
            'Ativo',
            'Baixado',
            'Em Proc de descarga',
            'Descarregado',
            'Entregue a COPAT',
            'Devolvido a locadora',
        ];

        $validated = $request->validate([
            'status'         => ['nullable', 'string', Rule::in($statusOptions)],
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
