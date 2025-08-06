@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Relatório de Manutenções Filtrado</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Veículo</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Data de Início</th>
                <th>Data de Fim</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manutencoes as $manutencao)
                <tr>
                    <td>{{ $manutencao->veiculo->marca_modelo ?? 'Não informado' }}</td>

                    <td>{{ ucfirst($manutencao->status) }}</td>
                    <td>{{ ucfirst($manutencao->tipo) }}</td>
                    <td>{{ $manutencao->descricao }}</td>
                    <td>{{ $manutencao->data_inicio->format('d/m/Y') }}</td>
                    <td>{{ $manutencao->data_fim ? $manutencao->data_fim->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.relatorios.manutencoes.filtros') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
