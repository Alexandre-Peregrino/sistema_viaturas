@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h2 class="text-primary mb-1">{{ $titulo ?? 'Relatório de Viaturas' }}</h2>
            <div class="text-muted small">
                Total nesta página: {{ $viaturas->count() }} | Total geral: {{ $viaturas->total() }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.relatorios.viaturas.filtros') }}" class="btn btn-outline-primary">
                Ajustar filtros
            </a>
            <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </div>

    {{-- Resumo de filtros aplicados --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge text-bg-secondary">
                    Escopo: {{ !empty($usarLotacao) ? 'Lotação atual' : 'Cadastro' }}
                </span>

                @if(request('opm_id'))
                    <span class="badge text-bg-secondary">OPM: {{ request('opm_id') }}</span>
                @else
                    <span class="badge text-bg-light text-dark border">OPM: Todas</span>
                @endif

                @php
                    $tipos = (array) request('tipos', []);
                    $comb = (array) request('combustiveis', []);
                    $trac = (array) request('tracoes', []);
                @endphp

                @if(count($tipos))
                    <span class="badge text-bg-light text-dark border">Tipos: {{ implode(', ', $tipos) }}</span>
                @endif

                @if(count($comb))
                    <span class="badge text-bg-light text-dark border">Combustíveis: {{ implode(', ', $comb) }}</span>
                @endif

                @if(count($trac))
                    <span class="badge text-bg-light text-dark border">Trações: {{ implode(', ', $trac) }}</span>
                @endif

                @if(!empty($mostrarTempo))
                    <span class="badge text-bg-info">Tempo: ativado</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($viaturas->isEmpty())
                <div class="p-4">
                    <div class="alert alert-warning mb-0">
                        Nenhuma viatura encontrada com os filtros atuais.
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Prefixo</th>
                                <th>Placa</th>
                                <th>Marca/Modelo</th>
                                <th>Tipo</th>
                                <th>Combustível</th>
                                <th>Tração</th>
                                <th>Status</th>
                                <th>OPM</th>
                                @if(!empty($usarLotacao) && !empty($mostrarTempo))
                                    <th>Desde</th>
                                    <th>Há quanto tempo</th>
                                @endif
                                <th class="text-center">Manut.</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($viaturas as $v)
                                @php
                                    // Quando usar lotação: controller carrega lotacoes atuais (data_saida null)
                                    $lotacaoAtual = (!empty($usarLotacao) && isset($v->lotacoes)) ? $v->lotacoes->first() : null;

                                    $opmSigla = null;
                                    if (!empty($usarLotacao) && $lotacaoAtual && $lotacaoAtual->opm) {
                                        $opmSigla = $lotacaoAtual->opm->sigla ?? null;
                                    } elseif ($v->opm) {
                                        $opmSigla = $v->opm->sigla ?? null;
                                    }

                                    // Tentativa de data de entrada (ajuste o nome do campo se for diferente)
                                    $desde = null;
                                    if ($lotacaoAtual) {
                                        $desde = $lotacaoAtual->data_entrada ?? $lotacaoAtual->created_at ?? null;
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $v->prefixo ?? '—' }}</td>
                                    <td class="fw-semibold">{{ $v->placa ?? '—' }}</td>
                                    <td>{{ $v->marca_modelo ?? ($v->marca ?? '').' '.($v->modelo ?? '') }}</td>
                                    <td>{{ $v->tipo_veiculo ?? '—' }}</td>
                                    <td>{{ $v->combustivel ?? '—' }}</td>
                                    <td>{{ $v->tracao ?? '—' }}</td>
                                    <td>{{ $v->status ?? '—' }}</td>
                                    <td>{{ $opmSigla ?? '—' }}</td>

                                    @if(!empty($usarLotacao) && !empty($mostrarTempo))
                                        <td>
                                            @if($desde)
                                                {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($desde)
                                                {{ \Carbon\Carbon::parse($desde)->diffForHumans() }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif

                                    <td class="text-center">
                                        <span class="badge text-bg-secondary">
                                            {{ $v->manutencoes_count ?? 0 }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.viaturas.edit', $v->id) }}" class="btn btn-sm btn-outline-primary">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $viaturas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
