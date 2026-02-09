@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Nova OPM</h4>

    <form method="POST" action="{{ route('admin.opms.store') }}" class="card card-body">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Sigla *</label>
                <input type="text" name="sigla" class="form-control" value="{{ old('sigla') }}" required>
                @error('sigla') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-8">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">CPR</label>
                <input type="text" name="cpr" class="form-control" value="{{ old('cpr', 'N/D') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Área</label>
                <input type="text" name="area" class="form-control" value="{{ old('area', 'N/D') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control" value="{{ old('cidade', 'N/D') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">OPM Pai</label>
                <select name="parent_opm_id" class="form-select">
                    <option value="">(Sem pai)</option>
                    @foreach($opmsPai as $p)
                        <option value="{{ $p->id }}" @selected(old('parent_opm_id') == $p->id)>
                            {{ $p->sigla }} — {{ $p->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('admin.opms.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection
