@extends('layouts.app')

@section('content')
<div class="container p-4 rounded shadow-sm" style="background-color: #F0F0F0;">
    <h1 class="mb-4 text-primary text-center">Editar Viatura (P4) - {{ $viatura->placa }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('p4.viaturas.update', $viatura->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Campos para visualização (desabilitados) --}}
        <div class="row mb-3">
            <div class="col">
                <label for="marca_modelo" class="form-label">Modelo:</label>
                <input type="text" class="form-control" value="{{ $viatura->marca_modelo }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
            <div class="col">
                <label for="ano_fabricacao" class="form-label">Ano de Fabricação:</label>
                <input type="number" class="form-control" value="{{ $viatura->ano_fabricacao }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="placa" class="form-label">Placa:</label>
                <input type="text" class="form-control" value="{{ $viatura->placa }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
            <div class="col">
                <label for="prefixo" class="form-label">Prefixo:</label>
                <input type="text" class="form-control" value="{{ $viatura->prefixo }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="opm_id" class="form-label">OPM:</label>
                <input type="text" class="form-control" value="{{ $viatura->opm->sigla ?? 'N/A' }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
            <div class="col">
                <label for="cidade" class="form-label">Cidade:</label>
                <input type="text" class="form-control" value="{{ $viatura->cidade }}" disabled style="background-color: #E2E2E2; border: 1px solid #A0A0A0;">
            </div>
        </div>

        {{-- Campos que o P4 PODE EDITAR --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="tipo_veiculo" class="form-label">Tipo Veículo:</label>
                <input type="text" class="form-control" name="tipo_veiculo" id="tipo_veiculo" value="{{ old('tipo_veiculo', $viatura->tipo_veiculo) }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
            <div class="col-md-6">
                <label for="situacao_carga" class="form-label">Situação Carga:</label>
                <input type="text" class="form-control" name="situacao_carga" id="situacao_carga" value="{{ old('situacao_carga', $viatura->situacao_carga) }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
        </div>

        <div class="mb-3">
            <label for="observacao" class="form-label">Observação:</label>
            <textarea class="form-control" name="observacao" id="observacao" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">{{ old('observacao', $viatura->observacao) }}</textarea>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="quilometragem" class="form-label">Quilometragem:</label>
                <input type="number" class="form-control" name="quilometragem" id="quilometragem" value="{{ old('quilometragem', $viatura->quilometragem) }}" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status:</label>
                <input type="text" class="form-control" name="status" id="status" value="{{ old('status', $viatura->status) }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a href="{{ route('p4.viaturas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
