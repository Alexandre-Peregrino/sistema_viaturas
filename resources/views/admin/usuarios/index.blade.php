@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Lista de Usuários</h1>

    {{-- Campo de busca --}}
    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mb-3 d-flex">
        <input type="text" name="busca" class="form-control me-2"
               placeholder="Buscar por nome, CPF ou matrícula"
               value="{{ request('busca') }}">
        <button type="submit" class="btn btn-primary">Buscar</button>
        @if(request('busca'))
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary ms-2">Limpar</a>
        @endif
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Perfil</th>
                <th>Permitido</th>
                <th>OPM</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $usuario->nome }}</td>
                    <td>{{ strtoupper($usuario->perfil) }}</td>
                    <td>{{ $usuario->permitido ? 'Sim' : 'Não' }}</td>
                    <td>{{ $usuario->opm->sigla ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir este usuário?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhum usuário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection
