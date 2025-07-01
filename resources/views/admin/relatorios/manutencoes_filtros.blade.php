@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Manutenções</h2>

    <form action="{{ route('admin.relatorios.manutencoes') }}" method="GET" class="row g-3">
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
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                <option value="aberta">Aberta</option>
                <option value="concluida">Concluída</option>
                <option value="pendente">Pendente</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="tipo" class="form-label">Tipo de Manutenção</label>
            <select name="tipo" class="form-select">
                <option value="">Todos</option>
                <option value="preventiva">Preventiva</option>
                <option value="corretiva">Corretiva</option>
            </select>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success">Gerar Relatório</button>
            <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
