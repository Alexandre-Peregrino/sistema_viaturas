@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Novo Usuário</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.usuarios.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" id="cpf" name="cpf" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="perfil" class="form-label">Perfil</label>
            <select name="perfil" id="perfil" class="form-select" required>
                <option value="">Selecione</option>
                <option value="admin">Administrador</option>
                <option value="p4">P4</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="opm_id" class="form-label">OPM</label>
            <select name="opm_id" id="opm_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach($opms as $opm)
                    <option value="{{ $opm->id }}">{{ $opm->sigla }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" id="senha" name="senha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="permitido" class="form-label">Permissão</label>
            <select name="permitido" id="permitido" class="form-select" required>
                <option value="1">Permitido</option>
                <option value="0">Bloqueado</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
