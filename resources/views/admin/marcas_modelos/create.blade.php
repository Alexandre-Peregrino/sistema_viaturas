@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-primary">Cadastrar Novo Modelo</h2>

    <form action="{{ route('admin.marcas_modelos.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca') }}" required>
            @error('marca')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo') }}" required>
            @error('modelo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.marcas_modelos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
