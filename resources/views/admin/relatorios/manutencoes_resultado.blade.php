@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Relatório de Manutenções Filtrado</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>OPM</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Data de Criação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manutencoes as $manutencao)
                <tr>
                    <td>{{ $manutencao->opm->nome ?? 'N/A' }}</td>
                    <td>{{ $manutencao->status }}</td>
                    <td>{{ $manutencao->tipo }}</td>
                    <td>{{ $manutencao->descricao }}</td>
                    <td>{{ $manutencao->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.relatorios.manutencoes.filtros') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
