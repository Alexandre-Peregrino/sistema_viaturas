@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Resultado do Relatório</h3>

  <div class="mb-2">
    <a href="{{ route('admin.relatorios.filtros_unificados') }}" class="btn btn-outline-secondary btn-sm">
      ← Voltar aos filtros
    </a>
  </div>

  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th>Prefixo</th>
        <th>Placa</th>
        <th>OPM</th>
        <th>Município (lotação)</th>
        <th>Desde</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    @forelse($veiculos as $v)
      @php $lot = $v->lotacoes->first(); @endphp
      <tr>
        <td>{{ $v->prefixo }}</td>
        <td>{{ $v->placa }}</td>
        <td>{{ optional($v->opm)->sigla }}</td>
        <td>{{ optional(optional($lot)->municipio)->nome }}</td>
        <td>{{ optional($lot)->data_entrada }}</td>
        <td>{{ $v->status ?? '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="6">Nenhum veículo encontrado para o filtro aplicado.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@endsection
