@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rádios - Administração</h2>
    <a href="{{ route('admin.radios.create') }}" class="btn btn-primary mb-3">Novo Rádio</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nº Série</th>
                <th>Modelo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($radios as $radio)
            <tr>
                <td>{{ $radio->numero_serie }}</td>
                <td>{{ $radio->modelo }}</td>
                <td>{{ $radio->status }}</td>
                <td>
                    <a href="{{ route('admin.radios.edit', $radio->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.radios.destroy', $radio->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover?')">Remover</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
