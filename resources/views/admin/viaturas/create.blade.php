<!-- resources/views/admin/viaturas/create.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Cadastrar Nova Viatura</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.viaturas.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <label for="marca_modelo" class="form-label">Marca/Modelo</label>
                <input type="text" class="form-control" name="marca_modelo" id="marca_modelo" required>
            </div>
            <div class="col">
                <label for="ano_fabricacao" class="form-label">Ano de Fabricação</label>
                <input type="number" class="form-control" name="ano_fabricacao" id="ano_fabricacao" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="placa" class="form-label">Placa</label>
                <input type="text" class="form-control" name="placa" id="placa" required>
            </div>
            <div class="col">
                <label for="prefixo" class="form-label">Prefixo</label>
                <input type="text" class="form-control" name="prefixo" id="prefixo">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="opm_id" class="form-label">OPM</label>
                <select name="opm_id" id="opm_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($opms as $opm)
                        <option value="{{ $opm->id }}">{{ $opm->sigla }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label for="cidade_municipio" class="form-label">Cidade/Município</label>
                <input type="text" class="form-control" name="cidade_municipio" id="cidade_municipio">
            </div>
        </div>

        <div class="mb-3">
            <label for="observacao" class="form-label">Observação</label>
            <textarea class="form-control" name="observacao" id="observacao"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.viaturas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection