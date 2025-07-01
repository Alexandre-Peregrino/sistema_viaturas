@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Viaturas</h2>

    <form action="{{ route('admin.relatorios.viaturas') }}" method="GET" class="row g-3">

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
            <label class="form-label">Tipo de Veículo</label>
            @foreach($tipos as $tipo)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipos[]" value="{{ $tipo }}" id="tipo_{{ $loop->index }}">
                    <label class="form-check-label" for="tipo_{{ $loop->index }}">{{ ucfirst($tipo) }}</label>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            <label class="form-label">Combustível</label>
            @foreach($combustiveis as $combustivel)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="combustiveis[]" value="{{ $combustivel }}" id="combustivel_{{ $loop->index }}">
                    <label class="form-check-label" for="combustivel_{{ $loop->index }}">{{ ucfirst($combustivel) }}</label>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            <label class="form-label">Tração</label>
            @foreach($tracoes as $tracao)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tracoes[]" value="{{ $tracao }}" id="tracao_{{ $loop->index }}">
                    <label class="form-check-label" for="tracao_{{ $loop->index }}">{{ strtoupper($tracao) }}</label>
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
