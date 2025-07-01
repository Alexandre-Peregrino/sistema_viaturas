@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="container p-4 shadow" style="max-width: 400px; background-color: #ffffff; border-radius: 15px;">

        {{-- Logotipo centralizado --}}
        <div class="text-center mb-3">
            <img src="{{ asset('images/breve_motomecanizacao.jpg') }}" alt="Logo" style="width: 80px;">
        </div>

        {{-- Título --}}
        <h2 class="mb-4 text-primary text-center">Acesso ao Sistema</h2>

        {{-- Mensagens de erro --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulário --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="cpf" class="form-label">CPF</label>
                <input id="cpf" type="text" class="form-control"
                       name="cpf" value="{{ old('cpf') }}" required autofocus
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input id="password" type="password" class="form-control"
                       name="password" required
                       style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Lembrar-me
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Entrar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
