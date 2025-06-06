@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Relatórios Gerais</h2>

    <a href="{{ route('admin.relatorios.viaturas') }}" class="btn btn-primary mb-3">Listar Todas as Viaturas</a>
    <form action="{{ route('relatorios.filtrar') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label for="opm_id" class="form-label">Filtrar por OPM</label>
            <select name="opm_id" class="form-select">
                <option value="">Todas</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}">{{ $opm->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-success">Filtrar</button>
        </div>
    </form>
</div>
@endsection
