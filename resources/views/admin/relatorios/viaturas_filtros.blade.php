@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Viaturas</h2>

    <form action="{{ route('admin.viaturas.relatorio') }}" method="GET" class="row g-3">

        <div class="col-md-4">
            <label for="opm_id" class="form-label">OPM</label>
            <select name="opm_id" id="opm_id" class="form-select">
                <option value="">Todas</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}" @selected(request('opm_id') == $opm->id)>
                        {{ $opm->sigla }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">
                Se “Usar lotação atual” estiver marcado, filtra pela OPM onde a viatura está <strong>lotada agora</strong>.
                Caso contrário, filtra pela OPM do <strong>cadastro</strong> da viatura.
            </small>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tipo de Veículo</label>
            @foreach($tipos as $tipo)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipos[]" value="{{ $tipo }}" id="tipo_{{ $loop->index }}"
                           @checked(collect(request('tipos'))->contains($tipo))>
                    <label class="form-check-label" for="tipo_{{ $loop->index }}">{{ ucfirst($tipo) }}</label>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            <label class="form-label">Combustível</label>
            @foreach($combustiveis as $combustivel)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="combustiveis[]" value="{{ $combustivel }}" id="combustivel_{{ $loop->index }}"
                           @checked(collect(request('combustiveis'))->contains($combustivel))>
                    <label class="form-check-label" for="combustivel_{{ $loop->index }}">{{ ucfirst($combustivel) }}</label>
                </div>
            @endforeach
        </div>

        <div class="col-md-4">
            <label class="form-label">Tração</label>
            @foreach($tracoes as $tracao)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tracoes[]" value="{{ $tracao }}" id="tracao_{{ $loop->index }}"
                           @checked(collect(request('tracoes'))->contains($tracao))>
                    <label class="form-check-label" for="tracao_{{ $loop->index }}">{{ strtoupper($tracao) }}</label>
                </div>
            @endforeach
        </div>

        {{-- NOVO: controles de lotação/tempo --}}
        <div class="col-md-12">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="usar_lotacao" id="usar_lotacao" value="1"
                       @checked(request()->boolean('usar_lotacao'))>
                <label class="form-check-label" for="usar_lotacao">Usar lotação atual</label>
            </div>

            <div class="form-check form-check-inline ms-3">
                <input class="form-check-input" type="checkbox" name="mostrar_tempo" id="mostrar_tempo" value="1"
                       @checked(request()->boolean('mostrar_tempo'))>
                <label class="form-check-label" for="mostrar_tempo">Mostrar “desde” / “há quanto tempo”</label>
            </div>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success">Gerar Relatório</button>
            <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>
@endsection
