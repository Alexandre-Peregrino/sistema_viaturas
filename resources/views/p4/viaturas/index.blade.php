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
                <th>Marca/Modelo</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($viaturas as $viatura)
                <tr>
                    <td>{{ $viatura->id }}</td>
                    <td>{{ $viatura->prefixo }}</td>
                    <td>{{ $viatura->placa }}</td>
                    <td>{{ $viatura->marca_modelo ?? '-' }}</td>
                    <td>{{ $viatura->tipo_veiculo ?? '-' }}</td>
                    <td>
                        <a href="{{ route('p4.viaturas.edit', $viatura) }}" class="btn btn-sm btn-warning">
                            Editar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhuma viatura cadastrada na sua OPM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(method_exists($viaturas, 'links'))
        <div class="mt-3">
            {{ $viaturas->links() }}
        </div>
    @endif
</div>
@endsection
