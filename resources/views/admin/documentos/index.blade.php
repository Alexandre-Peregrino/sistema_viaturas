@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Documentos da Viatura</h2>

    <a href="{{ route('admin.documentos.create') }}" class="btn btn-primary mb-3">Novo Documento</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Viatura</th>
                <th>Tipo</th>
                <th>Data Validade</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentos as $documento)
                <tr>
                    <td>{{ $documento->veiculo->prefixo ?? 'N/A' }}</td>
                    <td>{{ $documento->tipo }}</td>
                    <td>{{ $documento->validade }}</td>
                    <td>{{ $documento->status }}</td>
                    <td>
                        <a href="{{ route('admin.documentos.edit', $documento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.documentos.destroy', $documento->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
