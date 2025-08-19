@extends('layouts.app')

@section('content')
<div class="container p-4 rounded shadow-sm" style="background-color: #F0F0F0;">
    <h1 class="mb-4 text-primary text-center">
        Editar Viatura: {{ $viatura->prefixo }} ({{ $viatura->placa }})
    </h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.viaturas.update', $viatura->id) }}" method="POST" id="form-viatura-edit">
        @csrf
        @method('PUT')

        {{-- Linha 1 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="marca_modelo" class="form-label">Modelo (Marca/Modelo):</label>
                <input type="text" class="form-control" name="marca_modelo" id="marca_modelo"
                       value="{{ old('marca_modelo', $viatura->marca_modelo) }}" required>
            </div>
            <div class="col">
                <label for="ano_fabricacao" class="form-label">Ano de Fabricação:</label>
                <input type="number" class="form-control" name="ano_fabricacao" id="ano_fabricacao"
                       value="{{ old('ano_fabricacao', $viatura->ano_fabricacao) }}"
                       required min="1900" max="{{ date('Y') + 1 }}">
            </div>
        </div>

        {{-- Linha 2 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="placa" class="form-label">Placa:</label>
                <input type="text" class="form-control" name="placa" id="placa"
                       value="{{ old('placa', $viatura->placa) }}"
                       required maxlength="7" inputmode="latin">
                <small class="text-muted">Formato: ABC1234 ou ABC1D23 (sem hífen).</small>
            </div>
            <div class="col">
                <label for="prefixo" class="form-label">Prefixo:</label>
                <input type="text" class="form-control" name="prefixo" id="prefixo"
                       value="{{ old('prefixo', $viatura->prefixo) }}" required>
            </div>
        </div>

        {{-- Linha 3 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="chassi" class="form-label">Chassi:</label>
                <input type="text" class="form-control" name="chassi" id="chassi"
                       value="{{ old('chassi', $viatura->chassi) }}" required maxlength="17">
            </div>
            <div class="col">
                <label for="renavam" class="form-label">Renavam:</label>
                <input type="text" class="form-control" name="renavam" id="renavam"
                       value="{{ old('renavam', $viatura->renavam) }}" required>
            </div>
        </div>

        {{-- Linha 4 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="opm_id" class="form-label">OPM:</label>
                <select name="opm_id" id="opm_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($opms as $opm)
                        <option value="{{ $opm->id }}" {{ old('opm_id', $viatura->opm_id) == $opm->id ? 'selected' : '' }}>
                            {{ $opm->sigla }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label for="cidade" class="form-label">Cidade:</label>
                <input type="text" class="form-control" name="cidade" id="cidade"
                       value="{{ old('cidade', $viatura->cidade) }}" required>
            </div>
        </div>

        {{-- Linha 5 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="tipo_veiculo" class="form-label">Tipo Veículo:</label>
                <input type="text" class="form-control" name="tipo_veiculo" id="tipo_veiculo"
                       value="{{ old('tipo_veiculo', $viatura->tipo_veiculo) }}" required>
            </div>
            <div class="col">
                <label for="combustivel" class="form-label">Combustível:</label>
                <input type="text" class="form-control" name="combustivel" id="combustivel"
                       value="{{ old('combustivel', $viatura->combustivel) }}" required>
            </div>
        </div>

        {{-- Linha 6 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="situacao_carga" class="form-label">Situação Carga:</label>
                <input type="text" class="form-control" name="situacao_carga" id="situacao_carga"
                       value="{{ old('situacao_carga', $viatura->situacao_carga) }}" required>
            </div>
            <div class="col">
                <label for="emprego" class="form-label">Emprego:</label>
                <input type="text" class="form-control" name="emprego" id="emprego"
                       value="{{ old('emprego', $viatura->emprego) }}" required>
            </div>
        </div>

        {{-- Linha 7 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="tipo_uso" class="form-label">Tipo Uso:</label>
                <input type="text" class="form-control" name="tipo_uso" id="tipo_uso"
                       value="{{ old('tipo_uso', $viatura->tipo_uso) }}" required>
            </div>
            <div class="col">
                <label for="layout" class="form-label">Layout:</label>
                <input type="text" class="form-control" name="layout" id="layout"
                       value="{{ old('layout', $viatura->layout) }}" required>
            </div>
        </div>

        {{-- Linha 8 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="tracao" class="form-label">Tração:</label>
                <input type="text" class="form-control" name="tracao" id="tracao"
                       value="{{ old('tracao', $viatura->tracao) }}" required>
            </div>
            <div class="col">
                <label for="area" class="form-label">Área:</label>
                <input type="text" class="form-control" name="area" id="area"
                       value="{{ old('area', $viatura->area) }}" required>
            </div>
        </div>

        {{-- Linha 9 --}}
        <div class="row mb-3">
            <div class="col">
                <label for="categoria" class="form-label">Categoria:</label>
                <input type="text" class="form-control" name="categoria" id="categoria"
                       value="{{ old('categoria', $viatura->categoria) }}" required>
            </div>
            <div class="col">
                <label for="numero_serie_radio" class="form-label">Número de Série do Rádio (Opcional):</label>
                <select name="numero_serie_radio" id="numero_serie_radio" class="form-select">
                    <option value="">Nenhum Rádio</option>
                    @foreach($radios as $radio)
                        <option value="{{ $radio->numero_serie }}"
                            {{ old('numero_serie_radio', $viatura->numero_serie_radio) == $radio->numero_serie ? 'selected' : '' }}>
                            {{ $radio->numero_serie }} ({{ $radio->modelo }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Situação Operacional (radio) --}}
        <div class="row mb-3">
            <label class="form-label">Situação Operacional:</label>
            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="status" id="status_ativo" value="Ativo"
                           {{ old('status', $viatura->status) == 'Ativo' ? 'checked' : '' }}>
                    <label class="form-check-label" for="status_ativo">Ativo</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="status" id="status_descarga" value="Em Processo de Descarga"
                           {{ old('status', $viatura->status) == 'Em Processo de Descarga' ? 'checked' : '' }}>
                    <label class="form-check-label" for="status_descarga">Em Processo de Descarga</label>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('admin.viaturas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    (function(){
        const up = s => (s || '').toString().toUpperCase();

        const placa   = document.getElementById('placa');
        const prefixo = document.getElementById('prefixo');
        const chassi  = document.getElementById('chassi');

        // Placa sem hífen, 7 chars, uppercase
        placa.addEventListener('input', () => {
            let val = placa.value.replace(/[^A-Za-z0-9]/g, '');
            val = up(val).slice(0, 7);
            placa.value = val;
        });

        // Prefixo/chassi em uppercase; chassi limitado a 17
        prefixo.addEventListener('input', () => { prefixo.value = up(prefixo.value); });
        chassi.addEventListener('input', () => { chassi.value = up(chassi.value).slice(0, 17); });

        // Segurança extra no submit
        document.getElementById('form-viatura-edit').addEventListener('submit', function(){
            placa.value = up(placa.value).replace(/[^A-Z0-9]/g, '').slice(0,7);
            prefixo.value = up(prefixo.value);
            chassi.value = up(chassi.value).slice(0,17);
        });
    })();
</script>
@endsection
