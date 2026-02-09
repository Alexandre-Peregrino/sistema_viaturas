@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div>
            <h3 class="mb-0 text-primary"><i class="bi bi-search"></i> Consultas — Viaturas</h3>
            <div class="text-muted small">
                Combine filtros (AND). Dentro do mesmo filtro, múltiplas opções funcionam como OR.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('consultas.viaturas') }}">
                <i class="bi bi-eraser"></i> Limpar
            </a>

            <a class="btn btn-outline-success"
               href="{{ route('consultas.viaturas', array_merge(request()->query(), ['format' => 'csv'])) }}">
                <i class="bi bi-filetype-csv"></i> Exportar CSV
            </a>

            <button class="btn btn-primary" form="formFiltros">
                <i class="bi bi-funnel"></i> Aplicar
            </button>
        </div>
    </div>

    @if(!empty($activeChips))
        <div class="mb-3">
            @foreach($activeChips as $c)
                <span class="badge text-bg-light border me-1 mb-1">
                    <span class="text-muted">{{ $c['label'] }}:</span>
                    <span class="fw-semibold">{{ $c['value'] }}</span>
                </span>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
        {{-- FILTROS --}}
        <div class="col-12 col-lg-3 col-xl-2">
            <form id="formFiltros" method="GET" action="{{ route('consultas.viaturas') }}">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="fw-semibold">Filtros</div>
                        <div class="text-muted small">Dica: “Cidade (OPM)” ≠ “Cidade (Viatura)”.</div>
                    </div>

                    <div class="card-body">
                        {{-- Busca --}}
                        <label class="form-label fw-semibold">Busca rápida</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                               class="form-control" placeholder="Placa, prefixo, marca/modelo...">

                        <hr class="my-3">

                        {{-- Agrupamento --}}
                        <label class="form-label fw-semibold">Agrupar por</label>
                        <select name="group" class="form-select">
                            <option value="" @selected(empty($group))>(Sem agrupamento)</option>

                            <option value="opm"            @selected(($group ?? '') === 'opm')>Unidade (OPM)</option>
                            <option value="cpr"            @selected(($group ?? '') === 'cpr')>CPR</option>
                            <option value="opm_cidade"     @selected(($group ?? '') === 'opm_cidade')>Cidade (OPM)</option>
                            <option value="viatura_cidade" @selected(($group ?? '') === 'viatura_cidade')>Cidade (Viatura)</option>
                            <option value="area"           @selected(($group ?? '') === 'area')>Área</option>
                            <option value="ano_fab"        @selected(($group ?? '') === 'ano_fab')>Ano de Fabricação</option>
                            <option value="ano_mod"        @selected(($group ?? '') === 'ano_mod')>Ano de Modelo</option>
                            <option value="marca"          @selected(($group ?? '') === 'marca')>Marca</option>
                            <option value="tracao"         @selected(($group ?? '') === 'tracao')>Tração</option>
                        </select>

                        <hr class="my-3">

                        <div class="accordion" id="accFiltros">

                            {{-- Unidade --}}
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="hUnidade">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cUnidade">
                                        <i class="bi bi-diagram-3 me-2"></i> Unidade (OPM)
                                    </button>
                                </h2>
                                <div id="cUnidade" class="accordion-collapse collapse show" data-bs-parent="#accFiltros">
                                    <div class="accordion-body">

                                        <label class="form-label fw-semibold">OPM</label>

                                        <select name="opm_ids[]" id="opm_ids" class="form-select" multiple size="8"
                                                onchange="handleMultiWithAll('opm_ids')">

                                            <option value="__ALL__" @selected(empty($filters['opm_ids'] ?? []))>
                                                (Todas as unidades)
                                            </option>

                                            @foreach($options['opms'] as $opm)
                                                <option value="{{ $opm->id }}"
                                                    @selected(in_array((string)$opm->id, $filters['opm_ids'] ?? [], true))>
                                                    {{ $opm->sigla }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="form-text">
                                            Selecione uma ou mais unidades, ou “Todas as unidades”.
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Atributos --}}
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="hCarac">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cCarac">
                                        <i class="bi bi-car-front me-2"></i> Atributos
                                    </button>
                                </h2>
                                <div id="cCarac" class="accordion-collapse collapse" data-bs-parent="#accFiltros">
                                    <div class="accordion-body">

                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Ano Fab</label>
                                                <select name="anos_fab[]" id="anos_fab" class="form-select" multiple size="6"
                                                        onchange="handleMultiWithAll('anos_fab')">
                                                    <option value="__ALL__" @selected(empty($filters['anos_fab'] ?? []))>(Todos)</option>
                                                    @foreach($options['anos_fab'] as $x)
                                                        <option value="{{ $x->value }}"
                                                            @selected(in_array((string)$x->value, $filters['anos_fab'] ?? [], true))>
                                                            {{ $x->value }} ({{ $x->total }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Tração</label>
                                            <select name="tracoes[]" id="tracoes" class="form-select" multiple size="5"
                                                    onchange="handleMultiSpecial('tracoes')">
                                                <option value="__ALL__" @selected(empty($filters['tracoes'] ?? []))>(Todas)</option>
                                                <option value="__NOT_4X4__" @selected(in_array('__NOT_4X4__', $filters['tracoes'] ?? [], true))>
                                                    (Todas exceto 4x4)
                                                </option>

                                                @foreach($options['tracoes'] as $x)
                                                    <option value="{{ $x->value }}"
                                                        @selected(in_array((string)$x->value, $filters['tracoes'] ?? [], true))>
                                                        {{ $x->value }} ({{ $x->total }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Combustível</label>
                                            <select name="combustiveis[]" id="combustiveis" class="form-select" multiple size="5"
                                                    onchange="handleMultiWithAll('combustiveis')">
                                                <option value="__ALL__" @selected(empty($filters['combustiveis'] ?? []))>(Todos)</option>
                                                @foreach($options['combustiveis'] as $x)
                                                    <option value="{{ $x->value }}"
                                                        @selected(in_array((string)$x->value, $filters['combustiveis'] ?? [], true))>
                                                        {{ $x->value }} ({{ $x->total }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Tipo de Veículo</label>
                                            <select name="tipos[]" id="tipos" class="form-select" multiple size="5"
                                                    onchange="handleMultiWithAll('tipos')">
                                                <option value="__ALL__" @selected(empty($filters['tipos'] ?? []))>(Todos)</option>
                                                @foreach($options['tipos'] as $x)
                                                    <option value="{{ $x->value }}"
                                                        @selected(in_array((string)$x->value, $filters['tipos'] ?? [], true))>
                                                        {{ $x->value }} ({{ $x->total }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Cidade (Viatura)</label>
                                            <select name="viatura_cidades[]" id="viatura_cidades" class="form-select" multiple size="5"
                                                    onchange="handleMultiWithAll('viatura_cidades')">
                                                <option value="__ALL__" @selected(empty($filters['viatura_cidades'] ?? []))>(Todos)</option>
                                                @foreach($options['viatura_cidades'] as $x)
                                                    <option value="{{ $x->value }}"
                                                        @selected(in_array((string)$x->value, $filters['viatura_cidades'] ?? [], true))>
                                                        {{ $x->value }} ({{ $x->total }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Área</label>
                                            <select name="areas[]" id="areas" class="form-select" multiple size="5"
                                                    onchange="handleMultiWithAll('areas')">
                                                <option value="__ALL__" @selected(empty($filters['areas'] ?? []))>(Todos)</option>
                                                @foreach($options['areas'] as $x)
                                                    <option value="{{ $x->value }}"
                                                        @selected(in_array((string)$x->value, $filters['areas'] ?? [], true))>
                                                        {{ $x->value }} ({{ $x->total }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-3">
                                            <label class="form-label fw-semibold">Ativo</label>
                                            <select name="ativo" class="form-select">
                                                <option value=""  @selected(($filters['ativo'] ?? '') === '')>(Todos)</option>
                                                <option value="1" @selected(($filters['ativo'] ?? '') === '1')>Somente ativos</option>
                                                <option value="0" @selected(($filters['ativo'] ?? '') === '0')>Somente inativos</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <hr class="my-3">

                        <label class="form-label fw-semibold">Itens por página</label>
                        <select name="per_page" class="form-select">
                            @foreach([20, 50, 100, 200] as $n)
                                <option value="{{ $n }}" @selected((int)($perPage ?? 20) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>
            </form>
        </div>

        {{-- RESULTADOS --}}
        <div class="col-12 col-lg-9 col-xl-10">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total (filtro aplicado)</div>
                            <div class="fs-3 fw-bold">{{ $summary['total'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">{{ $summary['label'] ?? 'Resumo' }}</div>
                            <div class="small">Clique em um item do resumo para aplicar o filtro automaticamente (drill-down).</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumo agrupado --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold"><i class="bi bi-bar-chart-line me-2"></i> {{ $summary['label'] ?? 'Resumo' }}</div>
                    <div class="text-muted small">Top 200</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70%;">Item</th>
                                    <th class="text-end">Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($summary['rows'] ?? []) as $r)
                                    <tr>
                                        <td>
                                            <a class="text-decoration-none fw-semibold" href="{{ $r->drill_url }}">
                                                {{ $r->label }}
                                            </a>
                                            @if(property_exists($r, 'key_id') && $r->key_id)
                                                <span class="text-muted small ms-1">#{{ $r->key_id }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">{{ $r->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Sem dados para o resumo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Lista detalhada --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-semibold"><i class="bi bi-list-ul me-2"></i> Lista de viaturas</div>
                    <div class="text-muted small">
                        {{ $viaturas->total() }} encontradas
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Prefixo</th>
                                    <th>Placa</th>
                                    <th>Marca/Modelo</th>
                                    <th>Ano</th>
                                    <th>Tração</th>
                                    <th>Comb.</th>
                                    <th>Cidade</th>
                                    <th>Área</th>
                                    <th>OPM</th>
                                    <th class="text-center">Ativo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($viaturas as $v)
                                    <tr>
                                        <td class="fw-semibold">{{ $v->prefixo }}</td>
                                        <td>{{ $v->placa }}</td>
                                        <td>
                                            @php
                                                $mm = trim(($v->marca ?? '') . ' ' . ($v->modelo ?? ''));
                                                if ($mm === '') {
                                                    $mm = $v->marca_modelo ?? '-';
                                                }
                                            @endphp
                                            <div class="fw-semibold">{{ $mm }}</div>
                                            <div class="text-muted small">{{ $v->tipo_veiculo ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="small">Fab: {{ $v->ano_fabricacao ?? '-' }}</div>
                                            <div class="small text-muted">Mod: {{ $v->ano_modelo ?? '-' }}</div>
                                        </td>
                                        <td>{{ $v->tracao ?? '-' }}</td>
                                        <td>{{ $v->combustivel ?? '-' }}</td>
                                        <td>{{ $v->cidade ?? '-' }}</td>
                                        <td>{{ $v->area ?? '-' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $v->opm->sigla ?? '(sem OPM)' }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($v->ativo)
                                                <span class="badge bg-success">SIM</span>
                                            @else
                                                <span class="badge bg-secondary">NÃO</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Nenhuma viatura encontrada com os filtros atuais.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        {{ $viaturas->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleMultiWithAll(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const ALL = '__ALL__';
    const options = Array.from(select.options);

    const selectedValues = options.filter(o => o.selected).map(o => o.value);
    const allSelected = selectedValues.includes(ALL);
    const realSelected = selectedValues.filter(v => v !== ALL);

    // ✅ Se escolheu algum valor real, desmarca "Todos"
    if (realSelected.length > 0 && allSelected) {
        const allOpt = options.find(o => o.value === ALL);
        if (allOpt) allOpt.selected = false;
        return;
    }

    // ✅ Se clicou em "Todos", limpa os outros
    if (allSelected && realSelected.length > 0) {
        options.forEach(o => o.selected = (o.value === ALL));
        return;
    }

    // ✅ Se ficou sem nada, volta "Todos"
    if (selectedValues.length === 0) {
        const allOpt = options.find(o => o.value === ALL);
        if (allOpt) allOpt.selected = true;
    }
}

function handleMultiSpecial(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const ALL = '__ALL__';
    const NOT4X4 = '__NOT_4X4__';

    const options = Array.from(select.options);
    const selected = options.filter(o => o.selected).map(o => o.value);

    const hasAll = selected.includes(ALL);
    const hasNot4x4 = selected.includes(NOT4X4);
    const realSelected = selected.filter(v => v !== ALL && v !== NOT4X4);

    // ✅ Se marcou algum real (ex.: 4x4), desmarca ALL e NOT4X4
    if (realSelected.length > 0) {
        const allOpt = options.find(o => o.value === ALL);
        const notOpt = options.find(o => o.value === NOT4X4);
        if (allOpt) allOpt.selected = false;
        if (notOpt) notOpt.selected = false;
        return;
    }

    // ✅ Se marcou NOT4X4, desmarca ALL
    if (hasNot4x4 && hasAll) {
        const allOpt = options.find(o => o.value === ALL);
        if (allOpt) allOpt.selected = false;
        return;
    }

    // ✅ Se marcou ALL, desmarca NOT4X4
    if (hasAll && hasNot4x4) {
        const notOpt = options.find(o => o.value === NOT4X4);
        if (notOpt) notOpt.selected = false;
        return;
    }

    // ✅ Se ficou sem nada, volta ALL
    if (selected.length === 0) {
        const allOpt = options.find(o => o.value === ALL);
        if (allOpt) allOpt.selected = true;
    }
}
</script>
@endpush
