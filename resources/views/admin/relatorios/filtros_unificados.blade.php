@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Relatórios — Filtro Unificado</h3>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <form action="{{ route('admin.relatorios.resultado_unificado') }}" method="GET" id="form-filtros">
    <div class="mb-3">
      <label class="form-label d-block">Consultar por:</label>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_municipio" value="municipio">
        <label class="form-check-label" for="tipo_municipio">Município</label>
      </div>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_opm" value="opm">
        <label class="form-check-label" for="tipo_opm">OPM</label>
      </div>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_cpr" value="cpr">
        <label class="form-check-label" for="tipo_cpr">CPR</label>
      </div>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_cpc" value="cpc">
        <label class="form-check-label" for="tipo_cpc">CPC</label>
      </div>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_cpm" value="cpm">
        <label class="form-check-label" for="tipo_cpm">CPM</label>
      </div>

      {{-- Ative quando houver regra clara pra “diretoria” -> veículos
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" id="tipo_diretoria" value="diretoria">
        <label class="form-check-label" for="tipo_diretoria">Diretoria</label>
      </div>
      --}}
    </div>

    {{-- Selects dinâmicos (multi-select) --}}
    <div class="mb-3 d-none" id="wrap_municipio">
      <label class="form-label">Municípios</label>
      <select name="ids[]" id="select_municipio" class="form-select" multiple size="8">
        @foreach($municipios as $m)
          <option value="{{ $m->id }}">{{ $m->nome }}</option>
        @endforeach
      </select>
      <small class="text-muted">Segure CTRL (ou SHIFT) para selecionar vários.</small>
    </div>

    <div class="mb-3 d-none" id="wrap_opm">
      <label class="form-label">OPMs</label>
      <select name="ids[]" id="select_opm" class="form-select" multiple size="8">
        @foreach($opms as $o)
          <option value="{{ $o->id }}">{{ $o->sigla }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 d-none" id="wrap_cpr">
      <label class="form-label">CPRs</label>
      <select name="ids[]" id="select_cpr" class="form-select" multiple size="8">
        @foreach($regioes['CPR'] as $r)
          <option value="{{ $r->id }}">{{ $r->nome }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 d-none" id="wrap_cpc">
      <label class="form-label">CPC</label>
      <select name="ids[]" id="select_cpc" class="form-select" multiple size="6">
        @foreach($regioes['CPC'] as $r)
          <option value="{{ $r->id }}">{{ $r->nome }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 d-none" id="wrap_cpm">
      <label class="form-label">CPM</label>
      <select name="ids[]" id="select_cpm" class="form-select" multiple size="6">
        @foreach($regioes['CPM'] as $r)
          <option value="{{ $r->id }}">{{ $r->nome }}</option>
        @endforeach
      </select>
    </div>

    {{-- Diretorias opcional
    @if($diretorias->count())
    <div class="mb-3 d-none" id="wrap_diretoria">
      <label class="form-label">Diretorias</label>
      <select name="ids[]" id="select_diretoria" class="form-select" multiple size="8">
        @foreach($diretorias as $d)
          <option value="{{ $d->id }}">{{ $d->nome }}</option>
        @endforeach
      </select>
    </div>
    @endif
    --}}

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Gerar relatório</button>
    </div>
  </form>
</div>

{{-- JS simples para exibir o select conforme o tipo --}}
<script>
(function() {
  const wraps = {
    municipio: document.getElementById('wrap_municipio'),
    opm:       document.getElementById('wrap_opm'),
    cpr:       document.getElementById('wrap_cpr'),
    cpc:       document.getElementById('wrap_cpc'),
    cpm:       document.getElementById('wrap_cpm'),
    diretoria: document.getElementById('wrap_diretoria'),
  };

  function showOnly(which) {
    Object.values(wraps).forEach(el => el && el.classList.add('d-none'));
    if (which && wraps[which]) wraps[which].classList.remove('d-none');
  }

  document.querySelectorAll('input[name="tipo"]').forEach(r => {
    r.addEventListener('change', () => showOnly(r.value));
  });
})();
</script>
@endsection
