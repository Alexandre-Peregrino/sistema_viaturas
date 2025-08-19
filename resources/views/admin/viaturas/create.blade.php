@extends('layouts.app')

@section('content')
<div class="container p-4 rounded shadow-sm" style="background-color: #F0F0F0;">
    <h1 class="mb-4 text-primary text-center">Cadastrar Viatura</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.viaturas.store') }}" method="POST" id="form-viatura" novalidate>
        @csrf

        <div class="row mb-3">
            <div class="col">
                <label for="marca_modelo" class="form-label">Modelo (Marca/Modelo):</label>
                <input type="text" class="form-control" name="marca_modelo" id="marca_modelo"
                       value="{{ old('marca_modelo') }}" required>
            </div>
            <div class="col">
                <label for="ano_fabricacao" class="form-label">Ano de Fabricação:</label>
                <input type="number" class="form-control" name="ano_fabricacao" id="ano_fabricacao"
                       value="{{ old('ano_fabricacao') }}" required min="1900" max="{{ date('Y') + 1 }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="placa" class="form-label">Placa:</label>
                <input
                    type="text"
                    class="form-control"
                    name="placa"
                    id="placa"
                    value="{{ old('placa') }}"
                    required
                    maxlength="7"
                    pattern="^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');"
                    autocomplete="off"
                >
                <small class="text-muted">Formato: ABC1234 ou ABC1D23 (sem hífen).</small>
            </div>
            <div class="col">
                <label for="prefixo" class="form-label">Prefixo:</label>
                <input type="text" class="form-control" name="prefixo" id="prefixo"
                       value="{{ old('prefixo') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="chassi" class="form-label">Chassi:</label>
                <input type="text" class="form-control" name="chassi" id="chassi"
                       value="{{ old('chassi') }}" required maxlength="17"
                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');">
            </div>
            <div class="col">
                <label for="renavam" class="form-label">Renavam:</label>
                <input type="text" class="form-control" name="renavam" id="renavam"
                       value="{{ old('renavam') }}" required
                       oninput="this.value = this.value.replace(/[^0-9]/g,'');">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="opm_id" class="form-label">OPM:</label>
                <select name="opm_id" id="opm_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($opms as $opm)
                        <option value="{{ $opm->id }}" {{ old('opm_id') == $opm->id ? 'selected' : '' }}>
                            {{ $opm->sigla }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label for="cidade" class="form-label">Cidade:</label>
                <input type="text" class="form-control" name="cidade" id="cidade"
                       value="{{ old('cidade') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="tipo_veiculo" class="form-label">Tipo Veículo:</label>
                <input type="text" class="form-control" name="tipo_veiculo" id="tipo_veiculo"
                       value="{{ old('tipo_veiculo') }}" required>
            </div>
            <div class="col">
                <label for="combustivel" class="form-label">Combustível:</label>
                <input type="text" class="form-control" name="combustivel" id="combustivel"
                       value="{{ old('combustivel') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="situacao_carga" class="form-label">Situação Carga:</label>
                <input type="text" class="form-control" name="situacao_carga" id="situacao_carga"
                       value="{{ old('situacao_carga') }}" required>
            </div>
            <div class="col">
                <label for="emprego" class="form-label">Emprego:</label>
                <input type="text" class="form-control" name="emprego" id="emprego"
                       value="{{ old('emprego') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="tipo_uso" class="form-label">Tipo Uso:</label>
                <input type="text" class="form-control" name="tipo_uso" id="tipo_uso"
                       value="{{ old('tipo_uso') }}" required>
            </div>
            <div class="col">
                <label for="layout" class="form-label">Layout:</label>
                <input type="text" class="form-control" name="layout" id="layout"
                       value="{{ old('layout') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="tracao" class="form-label">Tração:</label>
                <input type="text" class="form-control" name="tracao" id="tracao"
                       value="{{ old('tracao') }}" required>
            </div>
            <div class="col">
                <label for="area" class="form-label">Área:</label>
                <input type="text" class="form-control" name="area" id="area"
                       value="{{ old('area') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="categoria" class="form-label">Categoria:</label>
                <input type="text" class="form-control" name="categoria" id="categoria"
                       value="{{ old('categoria') }}" required>
            </div>

            {{-- ÚLTIMA DIV CORRIGIDA: Número de Série do Rádio (Opcional) --}}
            <div class="col">
                <label for="numero_serie_radio" class="form-label">Número de Série do Rádio (Opcional):</label>
                @php($radiosDisponiveis = $radiosDisponiveis ?? collect())
                <select name="numero_serie_radio" id="numero_serie_radio" class="form-select">
                    <option value="">Sem rádio</option>
                    @foreach($radiosDisponiveis as $radio)
                        <option value="{{ $radio->numero_serie }}"
                                {{ old('numero_serie_radio') == $radio->numero_serie ? 'selected' : '' }}>
                            {{ $radio->numero_serie }} @if(!empty($radio->marca) || !empty($radio->modelo)) — {{ $radio->marca ?? '' }} {{ $radio->modelo ?? '' }} @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Opcional. Exibindo apenas rádios disponíveis.</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

{{-- Feedback de validação da Placa no cliente (HTML5 pattern já valida; abaixo só mensagem amigável) --}}
<script>
document.getElementById('form-viatura').addEventListener('submit', function(e) {
    const placa = document.getElementById('placa');
    const regex = /^([A-Z]{3}\d{4}|[A-Z]{3}\d[A-Z]\d{2})$/;
    if (!regex.test(placa.value)) {
        e.preventDefault();
        placa.focus();
        alert('Placa inválida. Use ABC1234 ou ABC1D23 (sem hífen).');
    }
});
</script>
@endsection
