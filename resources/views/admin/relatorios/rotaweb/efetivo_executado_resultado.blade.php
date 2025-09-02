@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-2"><i class="bi bi-graph-up-arrow"></i> RotaWeb • Efetivo Executado — Resultado</h3>
  <p class="text-muted">
    Operação: <strong>{{ $filtros['operacao'] }}</strong> |
    Período: <strong>{{ $filtros['inicio'] }}</strong> a <strong>{{ $filtros['termino'] }}</strong>
  </p>

  @if ($errors->has('rotaweb'))
    <div class="alert alert-danger">{{ $errors->first('rotaweb') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      @include('components.rotaweb.table', [
        'rows' => $dados,
        'tableId' => 'tbl-executado',
        'csvName' => 'efetivo-executado.csv'
      ])
    </div>
  </div>

  <a href="{{ route('admin.relatorios.rotaweb.efetivo_executado.filtros') }}" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left"></i> Voltar aos Filtros
  </a>
</div>
@endsection

@section('scripts')
  @stack('scripts') {{-- para o botão de export da tabela --}}
@endsection
