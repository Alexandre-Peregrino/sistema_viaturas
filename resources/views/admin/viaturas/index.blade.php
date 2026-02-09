@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="mb-0 text-primary">
                <i class="bi bi-pencil-square"></i> Viaturas — Cadastro
            </h3>
            <div class="text-muted small">
                Crie uma viatura nova ou abra rapidamente uma existente para editar.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.viaturas.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Nova Viatura
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

    {{-- =======================================================
         Acesso rápido: abrir para editar por PLACA (probe local)
         (reaproveita sua rota atual: admin.viaturas.db.probe)
       ======================================================= --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-search"></i> Abrir viatura para edição (por placa)</span>
            <small class="text-white-50">Vai buscar no banco local e permitir abrir o cadastro</small>
        </div>

        <div class="card-body">
            <form id="dbProbeForm" action="{{ route('admin.viaturas.db.probe') }}" method="POST" class="row g-3">
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
                    <div class="form-text">A placa será normalizada (maiúscula) automaticamente.</div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button id="dbProbeBtn" type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('admin.viaturas.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-eraser"></i> Limpar
                    </a>
                </div>
            </form>

            {{-- Resultado do BD --}}
            @if (session('db_veiculo'))
                @php $v = session('db_veiculo'); @endphp

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h6 class="mb-0">Encontrado</h6>

                    @if(isset($v->id))
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.viaturas.edit', $v->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i> Abrir para editar
                            </a>
                        </div>
                    @endif
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="w-25 text-muted">Placa</th>
                                    <td class="fw-semibold">{{ $v->placa ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Prefixo</th>
                                    <td>{{ $v->prefixo ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Marca/Modelo</th>
                                    <td>{{ $v->marca_modelo ?? ($v->marca ?? '—') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">OPM</th>
                                    <td>{{ $v->opm_id ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Ativo</th>
                                    <td>{{ isset($v->ativo) ? ($v->ativo ? 'SIM' : 'NÃO') : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Dica / orientação --}}
    <div class="alert alert-secondary">
        <div class="d-flex gap-2">
            <i class="bi bi-lightbulb"></i>
            <div>
                <div class="fw-semibold">Consultas e relatórios</div>
                <div class="small text-muted">
                    Para filtros avançados e listagens gerais, use a aba <strong>Consultas</strong>.
                    Aqui o foco é <strong>cadastro</strong> e <strong>alteração</strong>.
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Uppercase automático na placa
    document.getElementById('placa')?.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Spinner no botão
    const dbProbeForm = document.getElementById('dbProbeForm');
    const dbProbeBtn  = document.getElementById('dbProbeBtn');

    dbProbeForm?.addEventListener('submit', function () {
        if (!dbProbeBtn) return;
        dbProbeBtn.disabled = true;
        dbProbeBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Buscando...';
    });
</script>
@endsection
