@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Resultado do Relatório de Usuários</h2>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Perfil</th>
                <th>OPM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nome }}</td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ strtoupper($usuario->perfil) }}</td>
                    <td>{{ $usuario->opm->sigla ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Nenhum usuário encontrado com os critérios informados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('admin.relatorios.usuarios.filtros') }}" class="btn btn-secondary mt-3">Voltar aos Filtros</a>
</div>
@endsection
