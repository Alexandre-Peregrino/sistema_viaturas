@extends('layouts.app')

@section('content')
<div class="container p-4 rounded shadow-sm" style="background-color: #F0F0F0;">
    <h1 class="mb-4 text-primary text-center">Editar Rádio: {{ $radio->numero_serie }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.radios.update', $radio->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="numero_serie" class="form-label">Número de Série:</label>
                <input type="text" class="form-control" name="numero_serie" id="numero_serie"
                       value="{{ old('numero_serie', $radio->numero_serie) }}" required
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
            <div class="col-md-6">
                <label for="marca" class="form-label">Marca:</label>
                <input type="text" class="form-control" name="marca" id="marca"
                       value="{{ old('marca', $radio->marca) }}" required
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="modelo" class="form-label">Modelo:</label>
                <input type="text" class="form-control" name="modelo" id="modelo"
                       value="{{ old('modelo', $radio->modelo) }}" required
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status:</label>
                <input type="text" class="form-control" name="status" id="status"
                       value="{{ old('status', $radio->status) }}" required
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>
        </div>

        {{-- CAMPO: OPM (corrigido para exibir sigla e cidade) --}}
        <div class="mb-3">
            <label for="opm_id" class="form-label">OPM:</label>
            <select name="opm_id" id="opm_id" class="form-select"
                    style="background-color: #F8F8F8; border: 1px solid #A0A0A0;" required>
                <option value="">Selecione a OPM</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}"
                        {{ old('opm_id', $radio->opm_id) == $opm->id ? 'selected' : '' }}>
                        {{ $opm->sigla }} - {{ $opm->cidade }}
                    </option>
                @endforeach
            </select>
        </div>


        <div class="mb-3">
            <label for="observacao" class="form-label">Observação:</label>
            <textarea class="form-control" name="observacao" id="observacao"
                      style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">{{ old('observacao', $radio->observacao) }}</textarea>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('admin.radios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
