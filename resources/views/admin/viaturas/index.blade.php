@extends('layouts.app') {{-- Mantém o layout principal --}}

@section('content')
<div class="container mt-4" style="background-color: #f0f0f0; padding: 20px; border-radius: 8px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Viaturas - Administração</h1>
        <a href="{{ route('admin.viaturas.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nova Viatura
        </a>
    </div>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Se não houver viaturas --}}
    @if($viaturas->isEmpty())
        <div class="alert alert-info text-center">
            Nenhuma viatura cadastrada.
        </div>
    @else
        {{-- Filtro de exibição --}}
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggleLotacao">
                <label class="form-check-label" for="toggleLotacao">
                    Mostrar lotação atual (onde o veículo está)
                </label>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Prefixo</th>
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Tipo</th>
                        <th>OPM</th>
                        <th class="col-lotacao" style="display:none;">Lotação Atual</th>
                        <th>Cidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($viaturas as $viatura)
                    <tr>
                        <td>{{ $viatura->id }}</td>
                        <td>{{ $viatura->prefixo }}</td>
                        <td>{{ $viatura->placa }}</td>
                        <td>{{ $viatura->marca_modelo ?? '-' }}</td>
                        <td>{{ $viatura->tipo_veiculo ?? 'N/A' }}</td>
                        <td>{{ $viatura->opm->sigla ?? 'N/A' }}</td>
                        <td class="col-lotacao" style="display:none;">
                            @if($viatura->lotacoes && $viatura->lotacoes->isNotEmpty())
                                {{ $viatura->lotacoes->first()->opm->sigla ?? 'N/A' }}
                            @else
                                Não lotado
                            @endif
                        </td>
                        <td>{{ $viatura->opm->municipio->nome ?? $viatura->cidade ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.viaturas.edit', $viatura->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('admin.viaturas.destroy', $viatura->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmar exclusão da viatura {{ $viatura->prefixo }}?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Mostra/esconde a coluna de lotação
    document.getElementById('toggleLotacao')?.addEventListener('change', function() {
        const show = this.checked;
        document.querySelectorAll('.col-lotacao').forEach(el => {
            el.style.display = show ? '' : 'none';
        });
    });
</script>
@endsection
