@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Completar Cadastro</h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>CPF:</strong> {{ $user->cpf }}<br>
                        Complete seus dados para solicitar acesso ao sistema. Após enviar, um administrador irá analisar e liberar (ou não) as permissões.
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Corrija os campos abaixo:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('perfil.completar.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nome completo</label>
                                <input type="text" name="nome"
                                    class="form-control @error('nome') is-invalid @enderror"
                                    value="{{ old('nome', $user->nome) }}"
                                    required>
                                @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Posto / Graduação</label>
                                <input type="text" name="posto_graduacao"
                                    class="form-control @error('posto_graduacao') is-invalid @enderror"
                                    value="{{ old('posto_graduacao', $user->posto_graduacao) }}"
                                    placeholder="Ex.: SD, CB, 3ºSGT, TEN..."
                                    required>
                                @error('posto_graduacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Número Policial (NP)</label>
                                <input type="text" name="numero_praca"
                                    class="form-control @error('numero_praca') is-invalid @enderror"
                                    value="{{ old('numero_praca', $user->numero_praca) }}"
                                    required>
                                @error('numero_praca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">RG Militar</label>
                                <input type="text" name="rg_militar"
                                    class="form-control @error('rg_militar') is-invalid @enderror"
                                    value="{{ old('rg_militar', $user->rg_militar) }}"
                                    required>
                                @error('rg_militar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Matrícula</label>
                                <input type="text" name="matricula"
                                    class="form-control @error('matricula') is-invalid @enderror"
                                    value="{{ old('matricula', $user->matricula) }}"
                                    required>
                                @error('matricula') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telefone (WhatsApp)</label>
                                <input type="text" name="telefone"
                                    class="form-control @error('telefone') is-invalid @enderror"
                                    value="{{ old('telefone', $user->telefone) }}"
                                    placeholder="(84) 9xxxx-xxxx"
                                    required>
                                @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unidade (OPM)</label>
                                <select name="opm_id"
                                    class="form-select @error('opm_id') is-invalid @enderror"
                                    required>
                                    <option value="">Selecione...</option>
                                    @foreach ($opms as $opm)
                                        @php
                                            $label = trim(($opm->sigla ?? '') . ' - ' . ($opm->nome ?? ''));
                                            if (!empty($opm->cidade)) $label .= ' (' . $opm->cidade . ')';
                                        @endphp
                                        <option value="{{ $opm->id }}"
                                            @selected((string) old('opm_id', $user->opm_id) === (string) $opm->id)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('opm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('home.restrita') }}" class="btn btn-outline-secondary">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Enviar para aprovação
                            </button>
                        </div>
                    </form>

                    <p class="text-muted mt-3 mb-0 small">
                        Observação: completar este formulário <strong>não libera o acesso automaticamente</strong>.
                        Um administrador precisa aprovar seu perfil no sistema.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
