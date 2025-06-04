<!-- resources/views/admin/viaturas/index.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Lista de Viaturas</h1>

    <a href="{{ route('admin.viaturas.create') }}" class="btn btn-primary mb-3">Nova Viatura</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped table-bordered">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Prefixo</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>OPM</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($viaturas as $viatura)
                <tr>
                    <td>{{ $viatura->id }}</td>
                    <td>{{ $viatura->prefixo }}</td>
                    <td>{{ $viatura->placa }}</td>
                    <td>{{ $viatura->marca_modelo }}</td>
                    <td>{{ $viatura->opm->sigla ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.viaturas.edit', $viatura->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.viaturas.destroy', $viatura->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta viatura?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhuma viatura cadastrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection