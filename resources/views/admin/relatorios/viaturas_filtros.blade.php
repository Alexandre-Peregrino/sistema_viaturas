@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-primary mb-0">Filtrar Relatório de Viaturas</h2>
        <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.viaturas.relatorio') }}" method="GET" class="row g-3">

                {{-- Escopo --}}
                <div class="col-12">
                    <label class="form-label fw-semibold mb-1">Escopo da OPM</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="usar_lotacao"
                                id="usar_lotacao"
                                value="1"
                                @checked(request()->boolean('usar_lotacao'))
                            >
                            <label class="form-check-label" for="usar_lotacao">
                                Usar lotação atual
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="mostrar_tempo"
                                id="mostrar_tempo"
                                value="1"
                                @checked(request()->boolean('mostrar_tempo'))
                            >
                            <label class="form-check-label" for="mostrar_tempo">
                                Mostrar “desde” / “há quanto tempo”
                            </label>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Se “Usar lotação atual” estiver marcado, filtra pela OPM onde a viatura está <strong>lotada agora</strong>.
                        Caso contrário, filtra pela OPM do <strong>cadastro</strong> da viatura.
                    </small>
                </div>

                {{-- OPM --}}
                <div class="col-md-4">
                    <label for="opm_id" class="form-label fw-semibold">OPM</label>
                    <select name="opm_id" id="opm_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($opms as $opm)
                            <option value="{{ $opm->id }}" @selected((string)request('opm_id') === (string)$opm->id)>
                                {{ $opm->sigla }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipos --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo de Veículo</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                        @foreach($tipos as $tipo)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="tipos[]"
                                    value="{{ $tipo }}"
                                    id="tipo_{{ $loop->index }}"
                                    @checked(collect(request('tipos'))->contains($tipo))
                                >
                                <label class="form-check-label" for="tipo_{{ $loop->index }}">
                                    {{ ucfirst($tipo) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Combustível --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Combustível</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                        @foreach($combustiveis as $combustivel)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="combustiveis[]"
                                    value="{{ $combustivel }}"
                                    id="combustivel_{{ $loop->index }}"
                                    @checked(collect(request('combustiveis'))->contains($combustivel))
                                >
                                <label class="form-check-label" for="combustivel_{{ $loop->index }}">
                                    {{ ucfirst($combustivel) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tração --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tração</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                        @foreach($tracoes as $tracao)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="tracoes[]"
                                    value="{{ $tracao }}"
                                    id="tracao_{{ $loop->index }}"
                                    @checked(collect(request('tracoes'))->contains($tracao))
                                >
                                <label class="form-check-label" for="tracao_{{ $loop->index }}">
                                    {{ strtoupper($tracao) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Ações --}}
                <div class="col-12 mt-2 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        Gerar Relatório
                    </button>

                    <a href="{{ route('admin.viaturas.relatorio') }}" class="btn btn-outline-secondary">
                        Limpar filtros
                    </a>

                    <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const usarLotacao = document.getElementById('usar_lotacao');
    const mostrarTempo = document.getElementById('mostrar_tempo');

    function syncTempo() {
        if (!usarLotacao || !mostrarTempo) return;

        if (!usarLotacao.checked) {
            mostrarTempo.checked = false;
            mostrarTempo.disabled = true;
        } else {
            mostrarTempo.disabled = false;
        }
    }

    usarLotacao?.addEventListener('change', syncTempo);
    syncTempo();
})();
</script>
@endsection
