@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Gestão de Rádios</h1>
        <a href="{{ route('admin.radios.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Cadastrar Novo Rádio
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($radios->isEmpty())
        <div class="alert alert-info text-center">
            Nenhum rádio cadastrado.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Número de Série</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th>Observação</th> {{-- COLUNA DE CABEÇALHO PARA OBSERVAÇÃO --}}
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radios as $radio)
                    <tr>
                        <td>{{ $radio->id }}</td>
                        <td>{{ $radio->numero_serie }}</td>
                        <td>{{ $radio->marca ?? '-' }}</td>
                        <td>{{ $radio->modelo }}</td>
                        <td>{{ $radio->status }}</td>
                        <td>{{ $radio->observacao ?? '-' }}</td> {{-- ESTILO AMARELO REMOVIDO --}}
                        <td>
                            <a href="{{ route('admin.radios.edit', $radio->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('admin.radios.destroy', $radio->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmar exclusão do rádio {{ $radio->numero_serie }}?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
