@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Meus Documentos de Viaturas</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Viatura</th>
                <th>Tipo</th>
                <th>Validade</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documentos as $doc)
                <tr>
                    <td>{{ $doc->veiculo->prefixo ?? 'N/A' }} - {{ $doc->veiculo->placa ?? '' }}</td>
                    <td>{{ $doc->tipo }}</td>
                    <td>{{ \Carbon\Carbon::parse($doc->validade)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($doc->status) }}</td>
                    <td>
                        <a href="{{ route('p4.documentos.editar', $doc->id) }}" class="btn btn-sm btn-primary">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
