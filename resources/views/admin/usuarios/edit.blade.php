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
            {{-- Coluna: dados do usuário (agora editável) --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        Dados do Usuário (editável - banco local)
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                value="{{ old('nome', $usuario->nome) }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="nome_guerra" class="form-label">Nome de Guerra:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nome_guerra"
                                name="nome_guerra"
                                value="{{ old('nome_guerra', $usuario->nome_guerra) }}"
                                maxlength="120"
                                placeholder="Ex.: PEREGRINO"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="matricula" class="form-label">Matrícula:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="matricula"
                                name="matricula"
                                value="{{ old('matricula', $usuario->matricula) }}"
                                maxlength="50"
                                placeholder="Ex.: 123456"
                            >
                            <div class="form-text">
                                Se quiser manter imutável, mude para <code>readonly</code>.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="posto_graduacao" class="form-label">Posto/Graduação (campo novo):</label>
                            <input
                                type="text"
                                class="form-control"
                                id="posto_graduacao"
                                name="posto_graduacao"
                                value="{{ old('posto_graduacao', $usuario->posto_graduacao) }}"
                                maxlength="80"
                                placeholder="Ex.: 3º SGT"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="numero_praca" class="form-label">Número de Praça:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="numero_praca"
                                name="numero_praca"
                                value="{{ old('numero_praca', $usuario->numero_praca) }}"
                                maxlength="30"
                                placeholder="Ex.: 123.456"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="rg_militar" class="form-label">RG Militar:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="rg_militar"
                                name="rg_militar"
                                value="{{ old('rg_militar', $usuario->rg_militar) }}"
                                maxlength="50"
                                placeholder="Ex.: 0000000"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="telefone"
                                name="telefone"
                                value="{{ old('telefone', $usuario->telefone) }}"
                                maxlength="30"
                                placeholder="(84) 9xxxx-xxxx"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="cpf"
                                name="cpf"
                                value="{{ old('cpf', $usuario->cpf) }}"
                                maxlength="14"
                                readonly
                            >
                            <div class="form-text">
                                CPF está <strong>somente leitura</strong> por padrão (recomendado).
                                Se você realmente quiser editar, troque <code>readonly</code> por nada e trate no controller.
                            </div>
                        </div>

                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge {{ $usuario->permitido ? 'bg-success' : 'bg-danger' }}">
                                {{ $usuario->permitido ? 'Acesso permitido' : 'Acesso bloqueado' }}
                            </span>

                            <span class="badge bg-info text-dark">
                                Cadastro: {{ $usuario->cadastro_completo ? 'completo' : 'incompleto' }}
                            </span>

                            @if(!empty($usuario->solicitacao_status))
                                <span class="badge bg-warning text-dark">
                                    Solicitação: {{ $usuario->solicitacao_status }}
                                </span>
                            @endif
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

                        <div class="mb-3">
                            <label for="cadastro_completo" class="form-label">Cadastro Completo:</label>
                            <select name="cadastro_completo" id="cadastro_completo" class="form-select">
                                <option value="1" {{ old('cadastro_completo', $usuario->cadastro_completo) ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ !old('cadastro_completo', $usuario->cadastro_completo) ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="solicitacao_status" class="form-label">Status da Solicitação:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="solicitacao_status"
                                name="solicitacao_status"
                                value="{{ old('solicitacao_status', $usuario->solicitacao_status) }}"
                                maxlength="50"
                                placeholder="Ex.: pendente / aprovada / negada"
                            >
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
                    <strong>Admin/Super Admin</strong> pode editar os dados do usuário e as permissões locais.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
