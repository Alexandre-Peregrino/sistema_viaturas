@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 bg-white p-4 rounded shadow">
            <h4 class="mb-4 text-center text-primary">Login</h4>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input id="cpf" name="cpf" type="text" class="form-control @error('cpf') is-invalid @enderror" value="{{ old('cpf') }}" required autofocus>
                    @error('cpf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input id="senha" name="senha" type="password" class="form-control @error('senha') is-invalid @enderror" required>
                    @error('senha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>
@endsection