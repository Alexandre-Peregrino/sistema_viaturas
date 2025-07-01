@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Minhas Viaturas</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Prefixo</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Tipo</th>
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
                    <td>
                        <a href="{{ route('p4.viaturas.editar', $viatura->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhuma viatura cadastrada na sua OPM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
