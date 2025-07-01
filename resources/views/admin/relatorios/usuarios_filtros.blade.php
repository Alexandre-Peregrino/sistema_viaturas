@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Filtrar Relatório de Usuários</h2>

    <form action="#" method="GET" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Perfil</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="perfis[]" value="admin" id="perfil_admin">
                <label class="form-check-label" for="perfil_admin">Administrador</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="perfis[]" value="p4" id="perfil_p4">
                <label class="form-check-label" for="perfil_p4">P4</label>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">OPM</label>
            @foreach($opms as $opm)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="opms[]" value="{{ $opm->id }}" id="opm_{{ $opm->id }}">
                    <label class="form-check-label" for="opm_{{ $opm->id }}">{{ $opm->nome }}</label>
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
