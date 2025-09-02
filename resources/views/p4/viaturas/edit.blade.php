@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Editar Viatura #{{ $viatura->id }}</h1>

    <form action="{{ route('p4.viaturas.update', $viatura) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Prefixo</label>
            <input type="text" name="prefixo" class="form-control" value="{{ old('prefixo', $viatura->prefixo) }}" required>
            @error('prefixo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Marca/Modelo</label>
            <input type="text" name="marca_modelo" class="form-control" value="{{ old('marca_modelo', $viatura->marca_modelo) }}">
            @error('marca_modelo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de Veículo</label>
            <input type="text" name="tipo_veiculo" class="form-control" value="{{ old('tipo_veiculo', $viatura->tipo_veiculo) }}">
            @error('tipo_veiculo') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('p4.viaturas.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
