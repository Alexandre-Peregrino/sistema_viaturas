@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Minhas Manutenções</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Veículo</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manutencoes as $manutencao)
                <tr>
                    <td>{{ $manutencao->veiculo->placa ?? 'N/A' }}</td>
                    <td>{{ ucfirst($manutencao->tipo) }}</td>
                    <td>{{ $manutencao->descricao }}</td>
                    <td>{{ $manutencao->data_inicio }}</td>
                    <td>{{ $manutencao->data_fim ?? '-' }}</td>
                    <td>{{ $manutencao->status }}</td>
                    <td>
                        <a href="{{ route('p4.manutencoes.editar', $manutencao->id) }}" class="btn btn-primary btn-sm">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
