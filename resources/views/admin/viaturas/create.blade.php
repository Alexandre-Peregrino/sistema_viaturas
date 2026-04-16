@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="mb-0 text-primary"><i class="bi bi-plus-circle"></i> Cadastrar Veículo</h3>
            <div class="text-muted small">Preencha os dados conforme a estrutura do banco.</div>
        </div>

        <a href="{{ route('admin.viaturas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Corrija os erros abaixo:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            Dados do veículo
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.viaturas.store') }}" class="row g-3" id="formVeiculo">
                @csrf

                @include('admin.viaturas._form', [
                    'veiculo' => $veiculo ?? null,
                    // opcional: 'areas' => $areas ?? null,
                ])

                <div class="col-12 d-flex gap-2 mt-2">
                    <button class="btn btn-success">
                        <i class="bi bi-check2-circle"></i> Salvar
                    </button>
                    <a href="{{ route('admin.viaturas.index') }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection