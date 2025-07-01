@extends('layouts.app')

@section('content')
<div class="container mt-4 p-4 rounded shadow-sm" style="background-color: #f0f0f0;">
    <h1 class="mb-4 text-primary text-center">Editar Usuário: {{ $usuario->nome }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.usuarios.update', $usuario->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nome" class="form-label">Nome:</label>
            <input type="text" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="cpf" class="form-label">CPF:</label>
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $usuario->cpf) }}" class="form-control" required maxlength="11">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail:</label>
            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (opcional):</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label for="opm_id" class="form-label">OPM:</label>
            <select name="opm_id" id="opm_id" class="form-select" required>
                <option value="">Selecione a OPM</option>
                @foreach ($opms as $opm)
                    <option value="{{ $opm->id }}" {{ old('opm_id', $usuario->opm_id) == $opm->id ? 'selected' : '' }}>
                        {{ $opm->sigla }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="perfil" class="form-label">Perfil:</label>
            <select name="perfil" id="perfil" class="form-select" required>
                <option value="">Selecione o Perfil</option>
                @foreach ($perfis as $perfil)
                    <option value="{{ $perfil }}" {{ old('perfil', $usuario->perfil) == $perfil ? 'selected' : '' }}>
                        {{ ucfirst($perfil) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="permitido" class="form-label">Permissão de Acesso:</label>
            <select name="permitido" id="permitido" class="form-select" required>
                <option value="1" {{ old('permitido', $usuario->permitido) ? 'selected' : '' }}>Permitir</option>
                <option value="0" {{ !old('permitido', $usuario->permitido) ? 'selected' : '' }}>Bloquear</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cpfInput = document.getElementById('cpf');
        cpfInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
        });
    });
</script>
@endsection
