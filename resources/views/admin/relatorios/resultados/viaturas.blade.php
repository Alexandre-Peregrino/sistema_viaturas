@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">{{ $titulo ?? 'Relatório de Viaturas' }}</h2>

    @if($viaturas->isEmpty())
        <div class="alert alert-warning">
            Nenhuma viatura encontrada com os filtros selecionados.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Tipo</th>
                        <th>Combustível</th>
                        <th>Tração</th>
                        <th>OPM (Cadastro)</th>
                        @if(!empty($usarLotacao) && $usarLotacao)
                            <th>OPM (Lotação Atual)</th>
                            <th>Município (Lotação)</th>
                            @if(!empty($mostrarTempo) && $mostrarTempo)
                                <th>Desde</th>
                                <th>Há quanto tempo</th>
                            @endif
                        @endif
                        <th>Status</th>
                        <th>Qtd Manutenções</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($viaturas as $v)
                        @php
                            $lotacao = optional($v->lotacoes)->first(); // carregada com whereNull(data_saida)
                            $desde = $lotacao?->data_entrada ? \Carbon\Carbon::parse($lotacao->data_entrada) : null;
                        @endphp
                        <tr>
                            <td>{{ $v->placa }}</td>
                            <td>{{ $v->marca_modelo ?? 'Não informado' }}</td>
                            <td>{{ $v->tipo_veiculo ?? 'Não informado' }}</td>
                            <td>{{ $v->combustivel ?? 'Não informado' }}</td>
                            <td>{{ $v->tracao ?? 'Não informado' }}</td>
                            <td>{{ $v->opm->sigla ?? 'Não informado' }}</td>

                            @if(!empty($usarLotacao) && $usarLotacao)
                                <td>{{ $lotacao?->opm?->sigla ?? '—' }}</td>
                                <td>{{ $lotacao?->municipio?->nome ?? '—' }}</td>
                                @if(!empty($mostrarTempo) && $mostrarTempo)
                                    <td>{{ $desde ? $desde->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $desde ? $desde->diffForHumans(now(), true) : '—' }}</td>
                                @endif
                            @endif

                            <td>{{ ucfirst($v->status) }}</td>
                            <td>{{ $v->manutencoes_count ?? $v->manutencoes->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- paginação (se vier paginado) --}}
        @if(method_exists($viaturas, 'links'))
            {{ $viaturas->links() }}
        @endif
    @endif

    <a href="{{ route('admin.relatorios.viaturas.filtros') }}" class="btn btn-primary mt-3">Voltar aos Filtros</a>
</div>
@endsection
