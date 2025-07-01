@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Manutenção</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erros:</strong>
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('p4.manutencoes.atualizar', $manutencao->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Veículo</label>
            <input type="text" class="form-control" value="{{ $manutencao->veiculo->placa ?? 'N/A' }}" disabled>
        </div>

        <div class="mb-3">
            <label>Descrição</label>
            <input type="text" name="descricao" class="form-control" value="{{ $manutencao->descricao }}" required>
        </div>

        <div class="mb-3">
            <label>Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="{{ $manutencao->data_inicio }}" required>
        </div>

        <div class="mb-3">
            <label>Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="{{ $manutencao->data_fim }}">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ $manutencao->status }}" required>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('p4.manutencoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
