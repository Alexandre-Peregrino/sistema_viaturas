@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">{{ $titulo ?? 'Relatório de Viaturas' }}</h2>

    @if($viaturas->isEmpty())
        <div class="alert alert-warning">
            Nenhuma viatura encontrada com os filtros selecionados.
        </div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Tipo</th>
                    <th>Combustível</th>
                    <th>Tração</th>
                    <th>OPM</th>
                    <th>Status</th>
                    <th>Locação</th>
                    <th>Manutenções</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($viaturas as $v)
                <tr>
                    <td>{{ $v->placa }}</td>
                    <td>{{ $v->marca_modelo ?? 'Não informado' }}</td>
                    <td>{{ $v->tipo_veiculo ?? 'Não informado' }}</td>
                    <td>{{ $v->combustivel ?? 'Não informado' }}</td>
                    <td>{{ $v->tracao ?? 'Não informado' }}</td>
                    <td>{{ $v->opm->sigla ?? 'Não informado' }}</td>
                    <td>{{ ucfirst($v->status) }}</td>
                    <td>{{ $v->locada ? 'Locada' : 'Própria' }}</td>
                    <td>{{ $v->manutencoes->count() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('admin.relatorios.viaturas.filtros') }}" class="btn btn-primary mt-3">Voltar aos Filtros</a>
</div>
@endsection
