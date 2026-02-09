@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Bem-vindo ao Sistema de Viaturas</h5>
        </div>

        <div class="card-body">
            <p class="mb-2">
                Seu acesso ainda não foi liberado por um administrador.
            </p>

            <ul class="mb-3">
                <li><strong>CPF:</strong> {{ auth()->user()->cpf }}</li>
                <li><strong>Cadastro completo:</strong>
                    @if(auth()->user()->cadastro_completo)
                        <span class="badge bg-success">SIM</span>
                    @else
                        <span class="badge bg-warning text-dark">NÃO</span>
                    @endif
                </li>
                <li><strong>Status da solicitação:</strong> {{ auth()->user()->solicitacao_status ?? 'none' }}</li>
            </ul>

            @if(!auth()->user()->cadastro_completo)
                <a class="btn btn-primary" href="{{ route('perfil.completar') }}">
                    Completar cadastro
                </a>
            @else
                <button class="btn btn-secondary" disabled>
                    Aguardando aprovação do administrador
                </button>
            @endif
        </div>
    </div>
</div>
@endsection
