@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="mb-0 text-primary">
                <i class="bi bi-pencil-square"></i> Editar Viatura
            </h3>
            <div class="text-muted small">
                Placa: <strong>{{ $veiculo->placa }}</strong> • Prefixo: <strong>{{ $veiculo->prefixo }}</strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.viaturas.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-check-circle"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="bi bi-info-circle"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ocorreram erros na solicitação:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CARD: Dados do cadastro --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">
                <i class="bi bi-card-checklist me-2"></i> Dados do cadastro
            </div>
            <div class="text-muted small">
                ID: {{ $veiculo->id }}
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.viaturas.update', $veiculo->id) }}" class="row g-3">
                @csrf
                @method('PUT')

                @include('admin.viaturas._form', [
                    'veiculo' => $veiculo,
                    'opms' => $opms ?? null,
                    'radios' => $radios ?? null,
                    'radiosDisponiveis' => $radiosDisponiveis ?? null,
                    'cidades' => $cidades ?? null,
                    'areas' => $areas ?? null,
                    'municipioIds' => $municipioIds ?? null,
                ])

                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar alterações
                    </button>

                    <a href="{{ route('admin.viaturas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- CARD: Histórico de movimentações --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="fw-semibold">
                <i class="bi bi-clock-history me-2"></i> Histórico de movimentações
            </div>

            <button class="btn btn-outline-primary btn-sm"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#movimentacoesCollapse"
                    aria-expanded="false"
                    aria-controls="movimentacoesCollapse">
                <i class="bi bi-eye"></i> Ver/ocultar
            </button>
        </div>

        <div class="collapse" id="movimentacoesCollapse">
            <div class="card-body">
                <div class="text-muted small mb-3">
                    Este histórico é gerado a partir da tabela <code>veiculo_lotacoes</code>.
                    A lotação atual é a linha sem <code>data_saida</code>.
                </div>

                @include('admin.viaturas._movimentacoes', ['veiculo' => $veiculo])
            </div>
        </div>
    </div>

</div>
@endsection
