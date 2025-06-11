@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Viaturas - Administração</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.viaturas.create') }}" class="btn btn-primary mb-3">Nova Viatura</a>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Prefixo</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Tipo</th>
                <th>OPM</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($veiculos as $viatura)
                <tr>
                    <td>{{ $viatura->id }}</td>
                    <td>{{ $viatura->prefixo }}</td>
                    <td>{{ $viatura->placa }}</td>
                    <td>{{ $viatura->marcaModelo->modelo ?? '-' }}</td>
                    <td>{{ $viatura->tipoVeiculo->nome ?? '-' }}</td>
                    <td>{{ $viatura->opm->nome ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.viaturas.edit', $viatura->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.viaturas.destroy', $viatura->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmar exclusão?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhuma viatura cadastrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
