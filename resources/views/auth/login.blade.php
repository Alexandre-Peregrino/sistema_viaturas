@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="container p-4 shadow" style="max-width: 420px; background-color: #ffffff; border-radius: 15px;">

        {{-- Logotipo centralizado --}}
        <div class="text-center mb-3">
            <img src="{{ asset('images/breve_motomecanizacao.jpg') }}" alt="Logo" style="width: 80px;">
        </div>

        {{-- Título --}}
        <h2 class="mb-3 text-primary text-center">Acesso ao Sistema</h2>
        <p class="text-muted text-center mb-4" style="font-size: 0.95rem;">
            Use suas credenciais do <strong>AD (pm.govrn)</strong>.
        </p>

        {{-- Mensagens de status (ex.: logout, bloqueio, etc.) --}}
        @if (session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        {{-- Mensagens de erro --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulário --}}
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="cpf" class="form-label">CPF</label>
                <input
                    id="cpf"
                    type="text"
                    class="form-control @error('cpf') is-invalid @enderror"
                    name="cpf"
                    value="{{ old('cpf') }}"
                    required
                    autofocus
                    maxlength="11"
                    inputmode="numeric"
                    pattern="\d*"
                    placeholder="Somente números"
                    style="background-color: #F8F8F8; border: 1px solid #A0A0A0;"
                >
                @error('cpf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Digite 11 números (sem pontos ou traço).</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label d-flex justify-content-between align-items-center">
                    <span>Senha</span>
                    <button type="button" class="btn btn-sm btn-link p-0" id="togglePassword" aria-label="Mostrar/ocultar senha">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </label>
                <input
                    id="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    name="password"
                    required
                    style="background-color: #F8F8F8; border: 1px solid #A0A0A0;"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">A mesma senha do seu usuário no AD.</small>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Lembrar-me</label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Entrar
                </button>
            </div>

            {{-- Opcional: link de ajuda/suporte ou redefinição de senha do AD
            <div class="mt-3 text-center">
                <a href="#" class="small">Precisa de ajuda com sua senha?</a>
            </div>
            --}}
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Força CPF com apenas dígitos e máximo de 11
    const cpfInput = document.getElementById('cpf');
    cpfInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });

    // Mostrar/ocultar senha
    const toggleBtn  = document.getElementById('togglePassword');
    const pwdInput   = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', function () {
        const isPwd = pwdInput.getAttribute('type') === 'password';
        pwdInput.setAttribute('type', isPwd ? 'text' : 'password');
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
});
</script>
@endsection
