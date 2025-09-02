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

        <div class="row g-4">
            {{-- Coluna: dados do usuário (somente leitura) --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        Dados do Usuário (informativos)
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nome:</label>
                            <input type="text" class="form-control" value="{{ $usuario->nome }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome de Guerra:</label>
                            <input type="text" class="form-control" value="{{ $usuario->nome_guerra ?? '—' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Matrícula:</label>
                            <input type="text" class="form-control" value="{{ $usuario->matricula ?? '—' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Título/Graduação:</label>
                            <input type="text" class="form-control" value="{{ $usuario->titulo ?? '—' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CPF:</label>
                            <input type="text" class="form-control" value="{{ $usuario->cpf }}" disabled>
                        </div>

                        <div class="mt-2">
                            <span class="badge {{ $usuario->permitido ? 'bg-success' : 'bg-danger' }}">
                                {{ $usuario->permitido ? 'Acesso permitido' : 'Acesso bloqueado' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna: permissões locais (editável) --}}
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        Permissões Locais
                    </div>
                    <div class="card-body">
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
                            <label for="opm_id" class="form-label">OPM (Unidade):</label>
                            <select name="opm_id" id="opm_id" class="form-select" required>
                                <option value="">Selecione a OPM</option>
                                @foreach ($opms as $opm)
                                    <option value="{{ $opm->id }}" {{ old('opm_id', $usuario->opm_id) == $opm->id ? 'selected' : '' }}>
                                        {{ $opm->sigla }}{{ $opm->nome ? ' - '.$opm->nome : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- NOVO: e-mail editável --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail:</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="{{ old('email', $usuario->email) }}"
                                maxlength="255"
                                placeholder="nome@exemplo.com"
                                required
                            >
                            <div class="form-text">
                                Informe um e-mail válido. Este campo pode substituir o placeholder gerado no primeiro login.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="permitido" class="form-label">Permissão de Acesso:</label>
                            <select name="permitido" id="permitido" class="form-select">
                                <option value="1" {{ old('permitido', $usuario->permitido) ? 'selected' : '' }}>Permitir</option>
                                <option value="0" {{ !old('permitido', $usuario->permitido) ? 'selected' : '' }}>Bloquear</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Salvar Alterações
                        </button>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Perfil</strong>, <strong>OPM</strong> e <strong>E-mail</strong> são editáveis pelo administrador.
                    Os demais dados são informativos e podem ser atualizados em logins futuros ou manualmente pelo admin.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
