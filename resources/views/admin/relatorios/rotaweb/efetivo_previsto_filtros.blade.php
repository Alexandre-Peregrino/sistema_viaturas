@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-4"><i class="bi bi-clipboard-data"></i> RotaWeb • Efetivo Previsto</h3>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="GET" action="{{ route('admin.relatorios.rotaweb.efetivo_previsto.resultado') }}" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">ID da Operação</label>
          <input type="text" name="operacao" class="form-control" value="{{ old('operacao') }}" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Início</label>
          <input type="text" name="inicio" class="form-control" placeholder="YYYY-MM-DD ou YYYY-MM-DDTHH:MM" value="{{ old('inicio') }}" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Término</label>
          <input type="text" name="termino" class="form-control" placeholder="YYYY-MM-DD ou YYYY-MM-DDTHH:MM" value="{{ old('termino') }}" required>
        </div>
        <div class="col-12">
          <button class="btn btn-primary">
            <span class="me-1"><i class="bi bi-play-fill"></i></span> Gerar
          </button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.relatorios.geral') }}">Voltar</a>
        </div>
      </form>
    </div>
  </div>

  <small class="text-muted d-block mt-2">
    Dica: use <code>YYYY-MM-DD</code> (ex.: <code>2025-08-01</code>) ou <code>YYYY-MM-DDTHH:MM</code>.
  </small>
</div>
@endsection
