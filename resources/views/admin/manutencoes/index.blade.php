@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manutenções</h2>

    <a href="{{ route('admin.manutencoes.create') }}" class="btn btn-primary mb-3">Nova Manutenção</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Veículo</th>
                <th>Tipo</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($manutencoes as $manutencao)
                <tr>
                    <td>{{ $manutencao->veiculo->placa ?? 'N/A' }}</td>
                    <td>{{ ucfirst($manutencao->tipo) }}</td>
                    <td>{{ $manutencao->data_inicio }}</td>
                    <td>{{ $manutencao->data_fim ?? '-' }}</td>
                    <td>R$ {{ number_format($manutencao->valor, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($manutencao->status) }}</td>
                    <td>
                        <a href="{{ route('admin.manutencoes.edit', $manutencao->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.manutencoes.destroy', $manutencao->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Deseja excluir esta manutenção?')" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
