@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">OPMs</h4>
        <a href="{{ route('admin.opms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova OPM
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form class="card card-body mb-3" method="GET" action="{{ route('admin.opms.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Busca (sigla/nome)</label>
                <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">CPR</label>
                <select name="cpr" class="form-select">
                    <option value="">Todos</option>
                    @foreach($cprs as $item)
                        <option value="{{ $item }}" @selected(($cpr ?? '') === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Cidade</label>
                <select name="cidade" class="form-select">
                    <option value="">Todas</option>
                    @foreach($cidades as $item)
                        <option value="{{ $item }}" @selected(($cidade ?? '') === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button class="btn btn-secondary">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sigla</th>
                        <th>Nome</th>
                        <th>CPR</th>
                        <th>Área</th>
                        <th>Cidade</th>
                        <th class="text-center">Veículos</th>
                        <th class="text-center">Usuários</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opms as $opm)
                        <tr>
                            <td class="fw-semibold">{{ $opm->sigla }}</td>
                            <td>{{ $opm->nome }}</td>
                            <td>{{ $opm->cpr }}</td>
                            <td>{{ $opm->area }}</td>
                            <td>{{ $opm->cidade }}</td>
                            <td class="text-center">{{ $opm->veiculos_count }}</td>
                            <td class="text-center">{{ $opm->usuarios_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.opms.edit', $opm) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.opms.destroy', $opm) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta OPM?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">Nenhuma OPM encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $opms->links() }}
        </div>
    </div>
</div>
@endsection
