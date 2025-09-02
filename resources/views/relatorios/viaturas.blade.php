@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">{{ $titulo }}</h2>

    {{-- Filtros --}}
    <form method="GET" action="{{ url()->current() }}" class="row g-2 mb-3">
        <div class="col-auto">
            <label for="filtro_cpr" class="form-label mb-0">CPR</label>
            <select name="cpr" id="filtro_cpr" class="form-select">
                <option value="">Todas as CPR</option>
                @foreach ($cprs as $opt)
                    @php
                        $sel = (string)($cpr ?? request('cpr')) === (string)$opt ? 'selected' : '';
                    @endphp
                    <option value="{{ $opt }}" {{ $sel }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto align-self-end">
            <button class="btn btn-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </form>

    {{-- Tabela --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>OPM</th>
                    <th style="width: 130px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($viaturas as $viatura)
                    <tr>
                        <td>{{ $viatura->placa }}</td>
                        <td>{{ $viatura->modelo }}</td>
                        <td>{{ ucfirst($viatura->tipo) }}</td>
                        <td>{{ ucfirst($viatura->status) }}</td>
                        <td>
                            @php
                                $opm = $viatura->opm ?? null;
                            @endphp
                            {{ $opm?->sigla ?? $opm?->nome ?? '—' }}
                        </td>
                        <td>
                            <a href="{{ route('relatorios.detalhado', $viatura->id) }}"
                               class="btn btn-info btn-sm">
                                Ver Detalhes
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Nenhuma viatura encontrada para o filtro selecionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação (se $viaturas for paginator) --}}
    @if (method_exists($viaturas, 'links'))
        <div class="mt-3">
            {!! $viaturas->appends(request()->query())->links() !!}
        </div>
    @endif
</div>
@endsection
