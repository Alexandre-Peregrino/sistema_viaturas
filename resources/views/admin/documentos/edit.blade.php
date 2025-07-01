@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Editar Documento de Viatura</h2>

    <form action="{{ route('admin.documentos.update', $documento->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="veiculo_id" class="form-label">Viatura</label>
            <select name="veiculo_id" id="veiculo_id" class="form-select" required>
                @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}" {{ $documento->veiculo_id == $veiculo->id ? 'selected' : '' }}>
                        {{ $veiculo->prefixo }} - {{ $veiculo->placa }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de Documento</label>
            <input type="text" name="tipo" id="tipo" class="form-control" value="{{ $documento->tipo }}" required>
        </div>

        <div class="mb-3">
            <label for="validade" class="form-label">Validade</label>
            <input type="date" name="validade" id="validade" class="form-control" value="{{ $documento->validade }}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="válido" {{ $documento->status === 'válido' ? 'selected' : '' }}>Válido</option>
                <option value="vencido" {{ $documento->status === 'vencido' ? 'selected' : '' }}>Vencido</option>
                <option value="pendente" {{ $documento->status === 'pendente' ? 'selected' : '' }}>Pendente</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
