@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Rádios</h2>

    <form action="{{ route('admin.relatorios.radios') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label for="opm_id" class="form-label">OPM</label>
            <select name="opm_id" class="form-select">
                <option value="">Todas</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}">{{ $opm->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Situação</label>
            @foreach($situacoes as $situacao)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="situacoes[]" value="{{ $situacao }}" id="situacao_{{ $situacao }}">
                    <label class="form-check-label" for="situacao_{{ $situacao }}">{{ ucfirst($situacao) }}</label>
                </div>
            @endforeach
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success">Gerar Relatório</button>
            <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
