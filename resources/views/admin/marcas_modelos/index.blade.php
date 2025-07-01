<!-- resources/views/admin/marcas_modelos/index.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Modelos de Viaturas</h1>

    <a href="{{ route('admin.marcas_modelos.create') }}" class="btn btn-success mb-3">Novo Modelo</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Tipo</th>
                <th>Ano</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($marcasModelos as $modelo)
                <tr>
                    <td>{{ $modelo->id }}</td>
                    <td>{{ $modelo->marca }}</td>
                    <td>{{ $modelo->modelo }}</td>
                    <td>{{ $modelo->tipo }}</td>
                    <td>{{ $modelo->ano }}</td>
                    <td>
                        <a href="{{ route('admin.marcas_modelos.edit', $modelo->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.marcas_modelos.destroy', $modelo->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhum modelo cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
