<?php

namespace App\Http\Controllers;

use App\Models\Opm;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    private function user()
    {
        return auth()->user();
    }

    private function isAdmin(): bool
    {
        $u = $this->user();
        return $u && method_exists($u, 'isAdmin') ? (bool) $u->isAdmin() : false;
    }

    private function isP4(): bool
    {
        $u = $this->user();
        return $u && method_exists($u, 'isP4') ? (bool) $u->isP4() : false;
    }

    private function userOpmId(): ?int
    {
        $u = $this->user();
        if (!$u) return null;

        if (isset($u->opm_id) && is_numeric($u->opm_id)) {
            return (int) $u->opm_id;
        }

        if (method_exists($u, 'opm') && $u->opm) {
            return (int) $u->opm->id;
        }

        return null;
    }

    /**
     * Se há filtros “reais” para executar a consulta.
     * - Aceita paginação (page) como continuação de uma consulta já iniciada.
     * - Ignora group/per_page/format (não devem disparar consulta).
     */
    private function hasAnyRealFilter(Request $request, array $filters): bool
    {
        // Paginação conta (navegação de resultados)
        if ($request->filled('page')) return true;

        // Busca rápida
        if (!empty($filters['q'])) return true;

        // Arrays de filtros
        $arrayKeys = [
            'opm_ids',
            'cprs',
            'opm_cidades',
            'viatura_cidades',
            'areas',
            'anos_fab',
            'anos_mod',
            'marcas',
            'modelos',
            'tracoes',
            'combustiveis',
            'tipos',
            'status',
        ];

        foreach ($arrayKeys as $k) {
            if (!empty($filters[$k]) && is_array($filters[$k])) {
                return true;
            }
        }

        // Ativo: '' não conta
        if (($filters['ativo'] ?? '') !== '') return true;

        return false;
    }

    /**
     * GET /consultas/opms (JSON)
     */
    public function opms(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $cpr    = trim((string) $request->query('cpr', ''));
        $cidade = trim((string) $request->query('cidade', ''));
        $limit  = (int) $request->query('limit', 50);
        $limit  = ($limit > 0 && $limit <= 200) ? $limit : 50;

        $query = Opm::query()
            ->select(['id', 'sigla', 'nome', 'cpr', 'cidade'])
            ->when($q !== '', function ($qbuilder) use ($q) {
                $qbuilder->where(function ($w) use ($q) {
                    $w->where('sigla', 'ILIKE', "%{$q}%")
                        ->orWhere('nome', 'ILIKE', "%{$q}%");
                });
            })
            ->when($cpr !== '', fn($qb) => $qb->where('cpr', $cpr))
            ->when($cidade !== '', fn($qb) => $qb->where('cidade', $cidade))
            ->orderBy('sigla')
            ->limit($limit);

        return response()->json($query->get());
    }

    /**
     * GET /consultas/viaturas
     * - HTML (default)
     * - JSON quando Accept: application/json ou ?format=json
     * - CSV quando ?format=csv
     */
    public function viaturas(Request $request)
    {
        $format = (string) $request->query('format', '');

        if ($format === 'csv') {
            return $this->viaturasCsv($request);
        }

        if ($request->wantsJson() || $request->expectsJson() || $format === 'json') {
            return $this->viaturasJson($request);
        }

        return $this->viaturasHtml($request);
    }

    /**
     * JSON simples (para usos futuros/AJAX)
     */
    private function viaturasJson(Request $request)
    {
        $q     = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 30);
        $limit = ($limit > 0 && $limit <= 200) ? $limit : 30;

        $query = Veiculo::query()
            ->select([
                'id',
                'placa',
                'prefixo',
                'marca_modelo',
                'marca',
                'modelo',
                'tipo_veiculo',
                'combustivel',
                'tracao',
                'status',
                'ativo',
                'ano_fabricacao',
                'ano_modelo',
                'cidade',
                'area',
                'municipio_id',
                'opm_id',
            ])
            ->with([
                'opm:id,sigla,nome,cpr,cidade',
                'lotacaoAtual.opm:id,sigla,nome',  // ✅ Lotação oficial (OPM)
                'lotacaoAtual.municipio:id,nome',   // ✅ Município lotado oficial
                'municipio:id,nome'                 // ✅ Município direto da viatura
            ])

            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('placa', 'ILIKE', "%{$q}%")
                        ->orWhere('prefixo', 'ILIKE', "%{$q}%")
                        ->orWhere('marca_modelo', 'ILIKE', "%{$q}%")
                        ->orWhere('marca', 'ILIKE', "%{$q}%")
                        ->orWhere('modelo', 'ILIKE', "%{$q}%");
                });
            });

        // 🔒 Sigilo: veículos sigilosos só para admin
        if (!$this->isAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('nivel_sigilo')
                    ->orWhere('nivel_sigilo', '<>', 'sigiloso');
            });
        }

        // P4 restrito à própria OPM
        if ($this->isP4() && !$this->isAdmin()) {
            $userOpmId = $this->userOpmId();
            if ($userOpmId) {
                $query->where('opm_id', $userOpmId);
            }
        }

        return response()->json(
            $query->orderBy('prefixo')->limit($limit)->get()
        );
    }

    /**
     * HTML (tela principal)
     */
    private function viaturasHtml(Request $request)
    {
        $filters = $this->readFilters($request);
        $group = (string) $request->query('group', '');
        $groupAllowed = ['', 'opm', 'cpr', 'opm_cidade', 'viatura_cidade', 'area', 'ano_fab', 'ano_mod', 'marca', 'tracao'];
        if (!in_array($group, $groupAllowed, true)) $group = '';

        $perPage = (int) $request->query('per_page', 20);
        $perPage = ($perPage >= 10 && $perPage <= 200) ? $perPage : 20;

        $totalGeralQuery = Veiculo::query();
        if (!$this->isAdmin()) {
            $totalGeralQuery->where(function ($q) {
                $q->whereNull('nivel_sigilo')->orWhere('nivel_sigilo', '<>', 'sigiloso');
            });
        }
        $totalGeral = $totalGeralQuery->count();

        $executouConsulta = $this->hasAnyRealFilter($request, $filters);
        $options = $this->filterOptionsForViaturas();
        $activeChips = $this->makeActiveChips($filters);

        $selectedOpms = [];
        if (!empty($filters['opm_ids'])) {
            $selectedOpms = Opm::query()
                ->whereIn('id', array_map('intval', $filters['opm_ids']))
                ->orderBy('sigla')
                ->get(['id', 'sigla']);
        }

        if (!$executouConsulta && $group === '') {
            $empty = new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            return view('consultas.viaturas.index', [
                'filters' => $filters,
                'group' => $group,
                'perPage' => $perPage,
                'viaturas' => $empty,
                'summary' => null,
                'options' => $options,
                'activeChips' => $activeChips,
                'selectedOpms' => $selectedOpms,
                'executouConsulta' => false,
                'totalGeral' => $totalGeral,
            ]);
        }

        $baseQuery = $this->buildBaseQuery($filters);
        $q = $baseQuery->getQuery();
        $q->orders = null;

        $base = Veiculo::query()->fromSub($q, 'v')
            ->leftJoin('veiculo_lotacoes as l', function ($join) {
                $join->on('l.veiculo_id', 'v.id')->whereNull('l.data_saida');
            })
            ->leftJoin('opms as lot_o', 'lot_o.id', 'l.opm_id')
            ->leftJoin('opms as o', 'o.id', 'v.opm_id');

        $cprs = $filters['cprs'] ?? [];
        if (!empty($cprs)) {
            $cprFilter = "COALESCE(lot_o.cpr, o.cpr, '(sem CPR)') IN ('" . implode("','", array_map('addslashes', $cprs)) . "')";
            $base->havingRaw($cprFilter);
        }

        // ✅ GROUP BY COM TODAS AS COLUNAS
        $viaturas = $base
        ->select([
            'v.id',
            'v.placa',
            'v.prefixo',
            'v.marca_modelo',
            'v.marca',
            'v.modelo',
            'v.tipo_veiculo',
            'v.combustivel',
            'v.tracao',
            'v.status',
            'v.ativo',
            'v.ano_fabricacao',
            'v.ano_modelo',
            'v.cidade',
            'v.area',
            'v.municipio_id',
            'v.opm_id',
            'v.chassi',
            'v.renavam',
            'v.numero_serie_radio',
            DB::raw("COALESCE(lot_o.cpr, o.cpr, '(sem CPR)') as cpr"),
            DB::raw("COALESCE(lot_o.sigla, o.sigla, '(sem OPM)') as opm_sigla"),
        ])
        ->groupBy([
            'v.id',
            'v.placa',
            'v.prefixo',
            'v.marca_modelo',
            'v.marca',
            'v.modelo',
            'v.tipo_veiculo',
            'v.combustivel',
            'v.tracao',
            'v.status',
            'v.ativo',
            'v.ano_fabricacao',
            'v.ano_modelo',
            'v.cidade',
            'v.area',
            'v.municipio_id',
            'v.opm_id',
            'v.chassi',
            'v.renavam',
            'v.numero_serie_radio',
            'lot_o.cpr',
            'o.cpr',
            'lot_o.sigla',
            'o.sigla',
        ])
        ->orderBy('v.prefixo')
        ->simplePaginate($perPage)
        ->withQueryString();

        $summary = null;
        if ($group !== '') {
            $summary = $this->groupSummary(clone $base, $group, $filters);
        }

        return view('consultas.viaturas.index', [
            'filters' => $filters,
            'group' => $group,
            'perPage' => $perPage,
            'viaturas' => $viaturas,
            'summary' => $summary,
            'options' => $options,
            'activeChips' => $activeChips,
            'selectedOpms' => $selectedOpms,
            'executouConsulta' => true,
            'totalGeral' => $totalGeral,
        ]);
    }

    /**
     * CSV do resultado filtrado
     */
    private function viaturasCsv(Request $request): StreamedResponse
    {
        $filters = $this->readFilters($request);

        // ✅ Evita exportar "tudo" sem filtros
        if (!$this->hasAnyRealFilter($request, $filters)) {
            abort(422, 'Selecione ao menos um filtro antes de exportar CSV.');
        }

        $base = $this->buildBaseQuery($filters)
            ->orderBy('prefixo');

        $filename = 'consulta_viaturas_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($base) {
            $out = fopen('php://output', 'w');

            // BOM para Excel PT-BR (ajuda acentos)
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'prefixo',
                'placa',
                'marca',
                'modelo',
                'marca_modelo',
                'ano_fabricacao',
                'ano_modelo',
                'tracao',
                'combustivel',
                'tipo_veiculo',
                'cidade_viatura',
                'area',
                'opm_sigla',
                'opm_nome',
                'opm_cpr',
                'opm_cidade',
                'ativo',
                'status'
            ], ';');

            $base->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $v) {
                    fputcsv($out, [
                        $v->prefixo,
                        $v->placa,
                        $v->marca,
                        $v->modelo,
                        $v->marca_modelo,
                        $v->ano_fabricacao,
                        $v->ano_modelo,
                        $v->tracao,
                        $v->combustivel,
                        $v->tipo_veiculo,
                        $v->cidade,
                        $v->area,
                        optional($v->opm)->sigla,
                        optional($v->opm)->nome,
                        optional($v->opm)->cpr,
                        optional($v->opm)->cidade,
                        $v->ativo ? 'SIM' : 'NAO',
                        $v->status,
                    ], ';');
                }
            });

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    /**
     * Leitura/normalização de filtros (arrays sem vazios)
     * Aqui também removemos sentinelas "__ALL__" etc, para não "furar" filtros numéricos.
     */
    private function readFilters(Request $request): array
    {
        $filters = [
            'q'               => trim((string) $request->query('q', '')),

            'opm_ids'         => Arr::wrap($request->query('opm_ids', [])),
            'cprs'            => Arr::wrap($request->query('cprs', [])),
            'opm_cidades'     => Arr::wrap($request->query('opm_cidades', [])),

            'viatura_cidades' => Arr::wrap($request->query('viatura_cidades', [])),
            'areas'           => Arr::wrap($request->query('areas', [])),

            'anos_fab'        => Arr::wrap($request->query('anos_fab', [])),
            'anos_mod'        => Arr::wrap($request->query('anos_mod', [])),

            'marcas'          => Arr::wrap($request->query('marcas', [])),
            'modelos'         => Arr::wrap($request->query('modelos', [])),
            'tracoes'         => Arr::wrap($request->query('tracoes', [])),
            'combustiveis'    => Arr::wrap($request->query('combustiveis', [])),
            'tipos'           => Arr::wrap($request->query('tipos', [])),

            'status'          => Arr::wrap($request->query('status', [])),
            'ativo'           => $request->query('ativo', ''), // '', '1', '0'
        ];

        foreach ($filters as $k => $v) {
            if (is_array($v)) {
                // remove vazios
                $v = array_values(array_filter($v, fn($x) => (string) $x !== ''));

                // remove sentinela "__ALL__" (TODOS) — não deve virar filtro
                $v = array_values(array_filter($v, fn($x) => (string) $x !== '__ALL__'));

                $filters[$k] = $v;
            }
        }

        // Trações: mantém "__NOT_4X4__" se existir (é um especial válido)
        // "__ALL__" já foi removido acima
        if (!empty($filters['tracoes'])) {
            $filters['tracoes'] = array_values($filters['tracoes']);
        }

        return $filters;
    }

    /**
     * Query base com todos os filtros combináveis.
     * Usa apenas colunas que existem no schema real.
     */
    private function buildBaseQuery(array $filters)
    {
        $base = Veiculo::query()
            ->with([
                'opm:id,sigla,nome,cpr,cidade',
                'lotacaoAtual.opm:id,sigla,nome,cpr,cidade',  // ✅ Novo
            ]);
        $base->select([
            'id',
            'placa',
            'prefixo',
            'marca_modelo',
            'marca',
            'modelo',
            'tipo_veiculo',
            'combustivel',
            'tracao',
            'status',
            'ativo',
            'ano_fabricacao',
            'ano_modelo',
            'cidade',
            'area',
            'municipio_id',
            'opm_id',
            'chassi',
            'renavam',
            'numero_serie_radio'
        ]);

        // 🔒 Sigilo: veículos sigilosos só para admin
        if (!$this->isAdmin()) {
            $base->where(function ($q) {
                $q->whereNull('nivel_sigilo')
                    ->orWhere('nivel_sigilo', '<>', 'sigiloso');
            });
        }

        // Busca rápida (placa/prefixo/marca/modelo/tração/comb/… + OPM)
        $base->when($filters['q'] !== '', function ($qb) use ($filters) {
            $q = trim((string) $filters['q']);

            $qb->where(function ($w) use ($q) {
                $w->where('placa', 'ILIKE', "%{$q}%")
                    ->orWhere('prefixo', 'ILIKE', "%{$q}%")
                    ->orWhere('marca_modelo', 'ILIKE', "%{$q}%")
                    ->orWhere('marca', 'ILIKE', "%{$q}%")
                    ->orWhere('modelo', 'ILIKE', "%{$q}%")
                    ->orWhere('tipo_veiculo', 'ILIKE', "%{$q}%")
                    ->orWhere('combustivel', 'ILIKE', "%{$q}%")
                    ->orWhere('tracao', 'ILIKE', "%{$q}%")
                    ->orWhere('cidade', 'ILIKE', "%{$q}%")
                    ->orWhere('area', 'ILIKE', "%{$q}%")
                    ->orWhere('chassi', 'ILIKE', "%{$q}%")
                    ->orWhere('renavam', 'ILIKE', "%{$q}%")
                    ->orWhere('numero_serie_radio', 'ILIKE', "%{$q}%");
            });

            // Busca também na OPM (sigla/nome)
            $qb->orWhereHas('opm', function ($opmQ) use ($q) {
                $opmQ->where('sigla', 'ILIKE', "%{$q}%")
                    ->orWhere('nome', 'ILIKE', "%{$q}%");
            });
        })

            // CPR - baseado em campos da OPM
            ->when(!empty($filters['cprs']), function ($qb) use ($filters) {
                $qb->where(function ($q) use ($filters) {
                    // Prioridade: lotacaoAtual.opm
                    $q->whereHas('lotacaoAtual.opm', function ($opmQ) use ($filters) {
                        $opmQ->whereIn('cpr', $filters['cprs']);
                    })
                    // Fallback: v.opm
                    ->orWhereHas('opm', function ($opmQ) use ($filters) {
                        $opmQ->whereIn('cpr', $filters['cprs']);
                    });
                });
            })

            // OPM
            ->when(!empty($filters['opm_ids']), fn($qb) => $qb->whereIn('opm_id', array_map('intval', $filters['opm_ids'])))

            // Tipo
            ->when(!empty($filters['tipos']), fn($qb) => $qb->whereIn('tipo_veiculo', $filters['tipos']))

            // Combustível (agora seguro: "__ALL__" já foi removido em readFilters)
            ->when(!empty($filters['combustiveis']), fn($qb) => $qb->whereIn('combustivel', $filters['combustiveis']))

            // Tração: suporta especial "__NOT_4X4__"
            ->when(!empty($filters['tracoes']), function ($qb) use ($filters) {
                $vals = $filters['tracoes'];

                if (in_array('__NOT_4X4__', $vals, true)) {
                    $qb->whereNotNull('tracao')
                        ->where('tracao', '<>', '')
                        ->where('tracao', 'not ilike', '%4x4%');
                    return;
                }

                // valores reais (remove especial se vier misturado)
                $vals = array_values(array_filter($vals, fn($v) => $v !== '__NOT_4X4__'));
                if (!empty($vals)) {
                    $qb->whereIn('tracao', $vals);
                }
            })

            // Status
            ->when(!empty($filters['status']), fn($qb) => $qb->whereIn('status', $filters['status']))

            // Ano fabricação/modelo (seguro: "__ALL__" já removido antes de intval)
            ->when(!empty($filters['anos_fab']), fn($qb) => $qb->whereIn('ano_fabricacao', array_map('intval', $filters['anos_fab'])))
            ->when(!empty($filters['anos_mod']), fn($qb) => $qb->whereIn('ano_modelo', array_map('intval', $filters['anos_mod'])))

            // Marca/modelo
            ->when(!empty($filters['marcas']), fn($qb) => $qb->whereIn('marca', $filters['marcas']))
            ->when(!empty($filters['modelos']), fn($qb) => $qb->whereIn('modelo', $filters['modelos']))

            // Cidade/Área
            ->when(!empty($filters['viatura_cidades']), function ($qb) use ($filters) {
                // Busca os IDs dos municípios que correspondem aos nomes selecionados
                $municipioIds = \App\Models\Municipio::query()
                    ->whereIn('nome', $filters['viatura_cidades'])
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($municipioIds)) {
                    $qb->whereIn('municipio_id', $municipioIds);
                }
            })
            ->when(!empty($filters['areas']), function ($qb) use ($filters) {
                $qb->whereIn('area', $filters['areas']); // ✅ CORRETO
            })

            // Ativo
            ->when(($filters['ativo'] ?? '') !== '', function ($qb) use ($filters) {
                $qb->where('ativo', $filters['ativo'] === '1');
            });

        // filtros baseados em campos da OPM (CPR / cidade da unidade)
        if (!empty($filters['cprs']) || !empty($filters['opm_cidades'])) {
            $base->where(function ($q) use ($filters) {
                // Prioridade: lotacaoAtual.opm
                $q->whereHas('lotacaoAtual.opm', function ($opmQ) use ($filters) {
                    if (!empty($filters['cprs'])) {
                        $opmQ->whereIn('cpr', $filters['cprs']);
                    }
                    if (!empty($filters['opm_cidades'])) {
                        $opmQ->whereIn('cidade', $filters['opm_cidades']);
                    }
                })
                    // Fallback: v.opm
                    ->orWhereHas('opm', function ($opmQ) use ($filters) {
                        if (!empty($filters['cprs'])) {
                            $opmQ->whereIn('cpr', $filters['cprs']);
                        }
                        if (!empty($filters['opm_cidades'])) {
                            $opmQ->whereIn('cidade', $filters['opm_cidades']);
                        }
                    });
            });
        }

        // P4 restrito à própria OPM
        if ($this->isP4() && !$this->isAdmin()) {
            $userOpmId = $this->userOpmId();
            if ($userOpmId) {
                $base->where('opm_id', $userOpmId);
            }
        }

        return $base;
    }

    /**
     * Resumo agrupado + links de drill-down (clicar aplica filtro)
     */
    private function groupSummary($base, string $group, array $filters): array
    {
        $q = $base->getQuery();
        $q->orders = null;

        $total = (clone $base)->count();

        $builder = Veiculo::query()->fromSub($q, 'v');

        $rows = collect();
        $label = 'Resumo';

        switch ($group) {
            case 'cpr':
                $label = 'Por CPR';
                
                // Remove o filtro de CPR para o resumo mostrar TODAS as CPRs
                $filtrosParaResumo = $filters;
                unset($filtrosParaResumo['cprs']); // ← ESSENCIAL!
                
                $baseQueryParaResumo = $this->buildBaseQuery($filtrosParaResumo);
                
                $q = $baseQueryParaResumo->getQuery();
                $q->orders = null;
                
                $rows = Veiculo::query()
                    ->fromSub($q, 'v')
                    ->leftJoin('veiculo_lotacoes as l', fn($join) => $join->on('l.veiculo_id', 'v.id')->whereNull('l.data_saida'))
                    ->leftJoin('opms as lot_o', 'lot_o.id', 'l.opm_id')
                    ->leftJoin('opms as o', 'o.id', 'v.opm_id')
                    ->selectRaw("COALESCE(lot_o.cpr, o.cpr, '(sem CPR)') as label, COUNT(*) as total")
                    ->groupBy('label')
                    //->orderByDesc('total')      // Ordena pela quantidade
                    ->orderBy('label')          // Depois, alfabeticamente
                    ->limit(200)
                    ->get();
                break;

            case 'opm_cidade':
                $label = 'Por Cidade (Viatura)';
                
                // ADICIONE ESTAS LINHAS:
                $filtrosParaResumo = $filters;
                unset($filtrosParaResumo['viatura_cidades']); // ← ESTA LINHA É ESSENCIAL!
                
                // Depois use $filtrosParaResumo em vez de $filters:
                $baseQueryParaResumo = $this->buildBaseQuery($filtrosParaResumo);
                
                $q = $baseQueryParaResumo->getQuery();
                $q->orders = null;
                
                $rows = Veiculo::query()
                    ->fromSub($q, 'v')
                    ->join('municipios', 'municipios.id', '=', 'v.municipio_id')
                    ->selectRaw("municipios.nome as key_id, municipios.nome as label, COUNT(*) as total")
                    ->groupBy('municipios.nome')
                   
                    ->orderBy('municipios.nome')
                    ->limit(200)
                    ->get();
                break;

            case 'area':
                $label = 'Por Área';
                
                $filtrosParaResumo = $filters;
                unset($filtrosParaResumo['areas']); // ← ESSENCIAL!
                
                $baseQueryParaResumo = $this->buildBaseQuery($filtrosParaResumo);
                
                $q = $baseQueryParaResumo->getQuery();
                $q->orders = null;
                
                $rows = Veiculo::query()
                    ->fromSub($q, 'v')
                    ->selectRaw("COALESCE(v.area, '(sem Área)') as label, COUNT(*) as total")
                    ->groupBy('label')
                    ->orderByDesc('total')
                    ->orderBy('label')
                    ->limit(200)
                    ->get();
                break;

            case 'ano_fab':
                $label = 'Por Ano de Fabricação';
                $rows = $builder
                    ->selectRaw("COALESCE(v.ano_fabricacao::text,'(sem ano)') as label, COUNT(*) as total")
                    ->groupBy('v.ano_fabricacao')
                    ->orderByRaw("v.ano_fabricacao NULLS LAST")
                    ->limit(200)
                    ->get();
                break;

            case 'ano_mod':
                $label = 'Por Ano de Modelo';
                $rows = $builder
                    ->selectRaw("COALESCE(v.ano_modelo::text,'(sem ano)') as label, COUNT(*) as total")
                    ->groupBy('v.ano_modelo')
                    ->orderByRaw("v.ano_modelo NULLS LAST")
                    ->limit(200)
                    ->get();
                break;

            case 'marca':
                $label = 'Por Marca';
                $rows = $builder
                    ->selectRaw("COALESCE(v.marca,'(sem marca)') as label, COUNT(*) as total")
                    ->groupBy('v.marca')
                    ->orderByDesc('total')
                    ->limit(200)
                    ->get();
                break;

            case 'tracao':
                $label = 'Por Tração';
                $rows = $builder
                    ->selectRaw("COALESCE(v.tracao,'(sem tração)') as label, COUNT(*) as total")
                    ->groupBy('v.tracao')
                    ->orderByDesc('total')
                    ->limit(200)
                    ->get();
                break;
        }

        // links drill-down
        $rows = $rows->map(function ($r) use ($group) {
            $r->drill_url = $this->makeDrillUrl($group, $r);
            return $r;
        });

        return ['total' => $total, 'rows' => $rows, 'label' => $label];
    }

    private function makeDrillUrl(string $group, $row): string
    {
        $params = request()->query();

        $addToArrayParam = function (string $key, $value) use (&$params) {
            $current = Arr::wrap($params[$key] ?? []);
            $current = array_map('strval', $current);
            $val = (string) $value;

            if ($val === '' || str_starts_with($val, '(sem ')) return;

            if (!in_array($val, $current, true)) {
                $current[] = $val;
            }
            $params[$key] = $current;
        };

        switch ($group) {
            case 'opm':
                if (!empty($row->key_id)) $addToArrayParam('opm_ids', $row->key_id);
                break;
            case 'cpr':
                $params['cprs'] = [$row->label]; // ← Substitui em vez de adicionar
                break;
            case 'opm_cidade':
                $params['viatura_cidades'] = [$row->label];
                break;
            case 'area':
                $params['areas'] = [$row->label]; // ← Substitui em vez de adicionar
                break;
            case 'ano_fab':
                $addToArrayParam('anos_fab', $row->label);
                break;
            case 'ano_mod':
                $addToArrayParam('anos_mod', $row->label);
                break;
            case 'marca':
                $addToArrayParam('marcas', $row->label);
                break;
            case 'tracao':
                $addToArrayParam('tracoes', $row->label);
                break;
        }

        return route('consultas.viaturas', $params);
    }

    /**
     * Opções com contagem (para UX melhor)
     */
    private function filterOptionsForViaturas(): array
    {
        // Base para opções/contagens: deve respeitar sigilo e regra do P4
        $baseVeiculos = Veiculo::query();

        // 🔒 Sigilo: veículos sigilosos só para admin
        if (!$this->isAdmin()) {
            $baseVeiculos->where(function ($q) {
                $q->whereNull('nivel_sigilo')
                    ->orWhere('nivel_sigilo', '<>', 'sigiloso');
            });
        }

        // P4 restrito à própria OPM
        if ($this->isP4() && !$this->isAdmin()) {
            $userOpmId = $this->userOpmId();
            if ($userOpmId) $baseVeiculos->where('opm_id', $userOpmId);
        }

        $opmIdsInUse = (clone $baseVeiculos)->select('opm_id')->distinct()
            ->pluck('opm_id')->map(fn($x) => (int) $x)->all();

        $opms = Opm::query()
            ->whereIn('id', $opmIdsInUse)
            ->orderBy('sigla')
            ->get(['id', 'sigla', 'nome', 'cpr', 'cidade']);

        $cprs = Opm::query()
            ->whereIn('id', $opmIdsInUse)
            ->whereNotNull('cpr')->where('cpr', '<>', '')
            ->distinct()->orderBy('cpr')->pluck('cpr');

        $opmCidades = Opm::query()
            ->whereIn('id', $opmIdsInUse)
            ->whereNotNull('cidade')->where('cidade', '<>', '')
            ->distinct()->orderBy('cidade')->pluck('cidade');

        $listTextWithCount = function (string $col, int $limit = 200) use ($baseVeiculos) {
            return (clone $baseVeiculos)
                ->whereNotNull($col)
                ->where($col, '<>', '')
                ->selectRaw("{$col} as value, COUNT(*) as total")
                ->groupBy($col)
                ->orderByDesc('total')
                ->limit($limit)
                ->get();
        };

        $listNumberWithCount = function (string $col, int $limit = 200) use ($baseVeiculos) {
            return (clone $baseVeiculos)
                ->whereNotNull($col)
                ->selectRaw("{$col} as value, COUNT(*) as total")
                ->groupBy($col)
                ->orderByRaw("{$col} NULLS LAST")
                ->limit($limit)
                ->get();
        };

        return [
            'opms'        => $opms,
            'cprs'        => $cprs,
            'opm_cidades' => $opmCidades,

            'viatura_cidades' => $listTextWithCount('cidade', 200),
            'areas'           => $listTextWithCount('area', 200),

            'tipos'        => $listTextWithCount('tipo_veiculo', 200),
            'combustiveis' => $listTextWithCount('combustivel', 200),
            'tracoes'      => $listTextWithCount('tracao', 200),
            'status'       => $listTextWithCount('status', 200),

            'anos_fab'     => $listNumberWithCount('ano_fabricacao', 200),
            'anos_mod'     => $listNumberWithCount('ano_modelo', 200),

            'marcas'       => $listTextWithCount('marca', 200),
            'modelos'      => $listTextWithCount('modelo', 200),
        ];
    }

    private function makeActiveChips(array $filters): array
    {
        $chips = [];

        $add = function (string $label, $value) use (&$chips) {
            $v = is_array($value) ? implode(', ', $value) : (string) $value;
            $v = trim($v);
            if ($v === '') return;
            $chips[] = ['label' => $label, 'value' => $v];
        };

        $add('Busca', $filters['q'] ?? '');

        // OPM: mostrar SIGLA no chip
        $opmIds = $filters['opm_ids'] ?? [];
        if (!empty($opmIds)) {
            $siglas = Opm::query()
                ->whereIn('id', array_map('intval', $opmIds))
                ->orderBy('sigla')
                ->pluck('sigla')
                ->all();

            $add('OPM', $siglas);
        }

        $add('CPR', $filters['cprs'] ?? []);
        $add('Cidade (OPM)', $filters['opm_cidades'] ?? []);

        $add('Cidade (Viatura)', $filters['viatura_cidades'] ?? []);
        $add('Área', $filters['areas'] ?? []);

        $add('Ano Fab', $filters['anos_fab'] ?? []);
        $add('Ano Mod', $filters['anos_mod'] ?? []);

        $add('Marca', $filters['marcas'] ?? []);
        $add('Modelo', $filters['modelos'] ?? []);

        // Tração: remove especial no chip
        $tr = array_values(array_filter($filters['tracoes'] ?? [], fn($v) => $v !== '__NOT_4X4__'));
        $add('Tração', $tr);

        $add('Combustível', $filters['combustiveis'] ?? []);
        $add('Tipo', $filters['tipos'] ?? []);
        $add('Status', $filters['status'] ?? []);

        if (($filters['ativo'] ?? '') !== '') {
            $add('Ativo', ($filters['ativo'] === '1') ? 'Somente ativos' : 'Somente inativos');
        }

        return $chips;
    }
        /**
     * GET /consultas (com totalCount)
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'q', 'opm_ids', 'cprs', 'opm_cidades', 'viatura_cidades', 'areas', 
            'anos_fab', 'anos_mod', 'marcas', 'modelos', 'tracoes', 'combustiveis', 
            'tipos', 'status', 'ativo', 'group' // Adicione group se usado
        ]);

        if (!$this->hasAnyRealFilter($request, $filters)) {
            $totalCount = Veiculo::count(); // Total geral: 1454 (screenshot)
            $results = Veiculo::paginate(20); // Sem filtro, lista geral
            $chips = [];
        } else {
            $query = Veiculo::query();

            // Filtro usuário (não admin/P4)
            if (!$this->isAdmin() && !$this->isP4()) {
                $userOpmId = $this->userOpmId();
                if ($userOpmId) $query->where('opm_id', $userOpmId);
            }

            // Busca q
            if (!empty($filters['q'])) {
                $query->where(function ($w) use ($filters) {
                    $w->where('placa', 'ILIKE', "%{$filters['q']}%")
                    ->orWhere('prefixo', 'ILIKE', "%{$filters['q']}%")
                    ->orWhere('chassi', 'ILIKE', "%{$filters['q']}%")
                    ->orWhere('renavam', 'ILIKE', "%{$filters['q']}%");
                });
            }

            // Mapeamento filtro → coluna BD (corrigido do project_context)
            $columnMap = [
                'anos_fab' => 'ano_fabricacao',
                'anos_mod' => 'ano_modelo',
                'marcas' => 'marca',
                'modelos' => 'modelo',
                'tracoes' => 'tracao',
                'combustiveis' => 'combustivel',
                'areas' => 'area',
                'status' => 'status'
            ];
            foreach ($columnMap as $filterKey => $dbColumn) {
                if (!empty($filters[$filterKey]) && is_array($filters[$filterKey])) {
                    $values = array_filter(array_map('trim', $filters[$filterKey]));
                    $values = array_filter($values, fn($v) => $v !== '_ALL_'); // Ignora _ALL_
                    if (!empty($values)) {
                        $query->whereIn($dbColumn, $values);
                    }
                }
            }

            // OPMs especiais (JOIN ou whereIn opm_id)
            if (!empty($filters['opm_ids']) && is_array($filters['opm_ids'])) {
                $opmIds = array_filter($filters['opm_ids'], fn($v) => $v !== '_ALL_' && is_numeric($v));
                if (!empty($opmIds)) {
                    $query->whereIn('opm_id', array_map('intval', $opmIds));
                }
            }

            // Ativo
            if (($filters['ativo'] ?? '') !== '') {
                $query->where('ativo', $filters['ativo']);
            }

            // Outros (cpr, cidades): ajuste JOIN se preciso
            if (!empty($filters['cprs'])) $query->where('cpr', $filters['cprs'][0] ?? '');
            // viatura_cidades → cidade/municipio_id (exemplo)
            if (!empty($filters['viatura_cidades'])) $query->whereIn('cidade', $filters['viatura_cidades']);

            $results = $query->paginate(20); // Como screenshot (20 itens)
            $totalCount = $query->count(); // Agora 21 para 2023
            $chips = $this->makeActiveChips($filters);
        }

        $summary = ['total' => $totalCount];

        return view('consultas.index', compact('results', 'summary', 'chips'));
    }
}
