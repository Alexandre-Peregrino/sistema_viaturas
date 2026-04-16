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

    {{-- use o nome de rota DO resource: p4.manutencoes.update --}}
    <form action="{{ route('p4.manutencoes.update', $manutencao) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Veículo</label>
            <input type="text" class="form-control" value="{{ $manutencao->veiculo->placa ?? 'N/A' }}" disabled>
        </div>

        <div class="mb-3">
            <label>Descrição</label>
            <input type="text" name="descricao" class="form-control"
                   value="{{ old('descricao', $manutencao->descricao) }}" required>
        </div>

        <div class="mb-3">
            <label>Data Início</label>
            <input type="date" name="data_inicio" class="form-control"
                   value="{{ old('data_inicio', optional($manutencao->data_inicio)->format('Y-m-d')) }}" required>
        </div>

        <div class="mb-3">
            <label>Data Fim</label>
            <input type="date" name="data_fim" class="form-control"
                   value="{{ old('data_fim', optional($manutencao->data_fim)->format('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control"
                   value="{{ old('status', $manutencao->status) }}" required>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('p4.manutencoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
