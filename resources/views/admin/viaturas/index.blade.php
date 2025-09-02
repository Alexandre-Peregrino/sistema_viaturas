@extends('layouts.app') {{-- Mantém o layout principal --}}

@section('content')
<div class="container mt-4" style="background-color: #f0f0f0; padding: 20px; border-radius: 8px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Viaturas - Consulta</h1>
        <div class="d-flex gap-2">
            {{-- Se você ainda usa cadastro local, deixe este botão.
                 Caso NÃO use mais base local, pode remover o bloco abaixo. --}}
            <a href="{{ route('admin.viaturas.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Nova Viatura
            </a>
        </div>
    </div>

    {{-- Erro específico do ROTA --}}
    @if($errors->has('rota'))
        <div class="alert alert-danger">{{ $errors->first('rota') }}</div>
    @endif

    {{-- Alertas gerais --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
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

    {{-- ================================
         Consulta rápida no ROTA (por placa)
       ================================ --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>Consulta por Placa</span>
            <small class="text-white-50">Dica: informe apenas a <strong>placa</strong></small>
        </div>
        <div class="card-body">
            <form id="rotaProbeForm" action="{{ route('admin.viaturas.rota.probe') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="placa" class="form-label">Placa <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="placa"
                        id="placa"
                        class="form-control @error('placa') is-invalid @enderror"
                        placeholder="Ex: XXX0A00"
                        maxlength="12"
                        value="{{ old('placa') }}"
                        required
                        autocomplete="off"
                    >
                    @error('placa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button id="probeBtn" type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> Consultar
                    </button>
                </div>
            </form>

            {{-- Detalhes completos do veículo retornado pelo ROTA --}}
            @if (session('rota_veiculo'))
                @php
                    $v = session('rota_veiculo');
                    // Labels amigáveis para campos mais comuns
                    $labels = [
                        'placa'        => 'Placa',
                        'prefixo'      => 'Prefixo',
                        'renavam'      => 'RENAVAM',
                        'origem'       => 'Origem',
                        'combustivel'  => 'Combustível',
                        'observacao'   => 'Observação',
                        'modelo'       => 'Modelo',
                        'marca'        => 'Marca',
                        'orgao'        => 'Órgão',
                    ];
                @endphp

                <hr>
                <h6 class="mb-3">Detalhes do veículo (ROTA)</h6>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm mb-0">
                            <tbody>
                            @foreach($v as $key => $value)
                                <tr>
                                    <th class="w-25 text-muted">
                                        {{ $labels[$key] ?? \Illuminate\Support\Str::of($key)->replace('_', ' ')->title() }}
                                    </th>
                                    <td>
                                        @if (is_array($value))
                                            <pre class="mb-0 small">{{ json_encode($value, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ ($value === null || $value === '') ? '—' : $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Removido: tabela com viaturas do banco local --}}
</div>
@endsection

@section('scripts')
<script>
    // Uppercase automático na placa
    document.getElementById('placa')?.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Spinner no botão de consulta do ROTA
    const probeForm = document.getElementById('rotaProbeForm');
    const probeBtn  = document.getElementById('probeBtn');

    probeForm?.addEventListener('submit', function () {
        if (!probeBtn) return;
        probeBtn.disabled = true;
        probeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Consultando...';
    });
</script>
@endsection
