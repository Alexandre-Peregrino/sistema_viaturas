@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Novo Documento de Viatura</h2>

    <form action="{{ route('admin.documentos.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="veiculo_id" class="form-label">Viatura</label>
            <select name="veiculo_id" id="veiculo_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach($veiculos as $veiculo)
                    <option value="{{ $veiculo->id }}">{{ $veiculo->prefixo }} - {{ $veiculo->placa }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de Documento</label>
            <input type="text" name="tipo" id="tipo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="validade" class="form-label">Validade</label>
            <input type="date" name="validade" id="validade" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="válido">Válido</option>
                <option value="vencido">Vencido</option>
                <option value="pendente">Pendente</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
