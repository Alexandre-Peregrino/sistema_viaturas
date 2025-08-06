@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Rádios</h2>

    <form action="{{ route('admin.relatorios.radios') }}" method="GET" class="row g-3">
        {{-- Filtro por OPM --}}
        <div class="col-md-4">
            <label for="opm_id" class="form-label">OPM</label>
            <select name="opm_id" class="form-select">
                <option value="">Todas</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}">{{ $opm->sigla }} - {{ $opm->cidade }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtro por Marca --}}
        <div class="col-md-4">
            <label for="marca" class="form-label">Marca</label>
            <select name="marca" class="form-select">
                <option value="">Todas</option>
                @foreach($marcas as $marca)
                    <option value="{{ $marca }}">{{ $marca }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtro por Modelo --}}
        <div class="col-md-4">
            <label for="modelo" class="form-label">Modelo</label>
            <select name="modelo" class="form-select">
                <option value="">Todos</option>
                @foreach($modelos as $modelo)
                    <option value="{{ $modelo }}">{{ $modelo }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filtro por Situação (checkboxes) --}}
        <div class="col-12">
            <label class="form-label">Situação</label>
            <div class="row">
                @foreach($situacoes as $situacao)
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="situacoes[]" value="{{ $situacao }}" id="situacao_{{ $situacao }}">
                            <label class="form-check-label" for="situacao_{{ $situacao }}">{{ ucfirst($situacao) }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success">Gerar Relatório</button>
            <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
