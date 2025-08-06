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
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>ID</th>
                        <th>Número de Série</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th>OPM</th>
                        <th>Observação</th>
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
                        <td>{{ $radio->opm->sigla ?? 'N/A' }}</td>
                        <td>{{ $radio->observacao ?? '-' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.radios.edit', $radio->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.radios.destroy', $radio->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Confirmar exclusão do rádio {{ $radio->numero_serie }}?')">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
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
