{{--
    Este é um arquivo parcial para o formulário de Viatura.
    Ele é incluído nas views 'create.blade.php' e 'edit.blade.php'.
    A variável $viatura é passada para este parcial na view de edição,
    e para a view de criação, podemos passar um objeto Viatura vazio
    ou um array de valores 'old()' para preencher o formulário.
    Usamos $viatura-><campo> ?? old('<campo>') para preencher com o valor existente
    ou o valor antigo em caso de erro de validação, ou vazio se for um novo formulário.
--}}

<!-- Linha 1: Marca/Modelo, Ano de Fabricação -->
<div class="row mb-3">
    <div class="col">
        <label for="marca_modelo" class="form-label">Marca/Modelo:</label>
        <input type="text" class="form-control" name="marca_modelo" id="marca_modelo" value="{{ $viatura->marca_modelo ?? old('marca_modelo') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="ano_fabricacao" class="form-label">Ano de Fabricação:</label>
        <input type="number" class="form-control" name="ano_fabricacao" id="ano_fabricacao" value="{{ $viatura->ano_fabricacao ?? old('ano_fabricacao') }}" required min="1900" max="{{ date('Y') + 1 }}" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 2: Placa, Prefixo -->
<div class="row mb-3">
    <div class="col">
        <label for="placa" class="form-label">Placa:</label>
        <input type="text" class="form-control" name="placa" id="placa" value="{{ $viatura->placa ?? old('placa') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="prefixo" class="form-label">Prefixo:</label>
        <input type="text" class="form-control" name="prefixo" id="prefixo" value="{{ $viatura->prefixo ?? old('prefixo') }}" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 3: Chassi, Renavam -->
<div class="row mb-3">
    <div class="col">
        <label for="chassi" class="form-label">Chassi:</label>
        <input type="text" class="form-control" name="chassi" id="chassi" value="{{ $viatura->chassi ?? old('chassi') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="renavam" class="form-label">Renavam:</label>
        <input type="text" class="form-control" name="renavam" id="renavam" value="{{ $viatura->renavam ?? old('renavam') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 4: OPM, Cidade -->
<div class="row mb-3">
    <div class="col">
        <label for="opm_id" class="form-label">OPM:</label>
        <select name="opm_id" id="opm_id" class="form-select" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            <option value="">Selecione</option>
            @foreach($opms as $opm)
                <option value="{{ $opm->id }}" {{ ($viatura->opm_id ?? old('opm_id')) == $opm->id ? 'selected' : '' }}>{{ $opm->sigla }}</option>
            @endforeach
        </select>
    </div>
    <div class="col">
        <label for="cidade" class="form-label">Cidade:</label>
        <input type="text" class="form-control" name="cidade" id="cidade" value="{{ $viatura->cidade ?? old('cidade') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 5: Tipo Veículo, Combustível -->
<div class="row mb-3">
    <div class="col">
        <label for="tipo_veiculo" class="form-label">Tipo Veículo:</label>
        <input type="text" class="form-control" name="tipo_veiculo" id="tipo_veiculo" value="{{ $viatura->tipo_veiculo ?? old('tipo_veiculo') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="combustivel" class="form-label">Combustível:</label>
        <input type="text" class="form-control" name="combustivel" id="combustivel" value="{{ $viatura->combustivel ?? old('combustivel') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 6: Situação Carga, Emprego -->
<div class="row mb-3">
    <div class="col">
        <label for="situacao_carga" class="form-label">Situação Carga:</label>
        <input type="text" class="form-control" name="situacao_carga" id="situacao_carga" value="{{ $viatura->situacao_carga ?? old('situacao_carga') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="emprego" class="form-label">Emprego:</label>
        <input type="text" class="form-control" name="emprego" id="emprego" value="{{ $viatura->emprego ?? old('emprego') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 7: Tipo Uso, Layout -->
<div class="row mb-3">
    <div class="col">
        <label for="tipo_uso" class="form-label">Tipo Uso:</label>
        <input type="text" class="form-control" name="tipo_uso" id="tipo_uso" value="{{ $viatura->tipo_uso ?? old('tipo_uso') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="layout" class="form-label">Layout:</label>
        <input type="text" class="form-control" name="layout" id="layout" value="{{ $viatura->layout ?? old('layout') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 8: Tração, Área -->
<div class="row mb-3">
    <div class="col">
        <label for="tracao" class="form-label">Tração:</label>
        <input type="text" class="form-control" name="tracao" id="tracao" value="{{ $viatura->tracao ?? old('tracao') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="area" class="form-label">Área:</label>
        <input type="text" class="form-control" name="area" id="area" value="{{ $viatura->area ?? old('area') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Linha 9: Categoria, Número de Série do Rádio (Select) -->
<div class="row mb-3">
    <div class="col">
        <label for="categoria" class="form-label">Categoria:</label>
        <input type="text" class="form-control" name="categoria" id="categoria" value="{{ $viatura->categoria ?? old('categoria') }}" required style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="numero_serie_radio" class="form-label">Número de Série do Rádio (Opcional):</label>
        <select name="numero_serie_radio" id="numero_serie_radio" class="form-select" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
            <option value="">Nenhum Rádio</option>
            @foreach($radios as $radio)
                <option value="{{ $radio->numero_serie }}" {{ ($viatura->numero_serie_radio ?? old('numero_serie_radio')) == $radio->numero_serie ? 'selected' : '' }}>
                    {{ $radio->numero_serie }} ({{ $radio->modelo }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Linha 10: Ativo, Em Processo de Descarga (Checkboxes) -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-check">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ ($viatura->ativo ?? old('ativo', true)) ? 'checked' : '' }}>
            <label for="ativo" class="form-check-label">Ativo</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check">
            <input type="hidden" name="em_processo_descarga" value="0">
            <input type="checkbox" name="em_processo_descarga" id="em_processo_descarga" class="form-check-input" value="1" {{ ($viatura->em_processo_descarga ?? old('em_processo_descarga', false)) ? 'checked' : '' }}>
            <label for="em_processo_descarga" class="form-check-label">Em Processo de Descarga</label>
        </div>
    </div>
</div>

<!-- Linha 11: Aquisição Dados, Entrega Dados OPM (Datas) -->
<div class="row mb-3">
    <div class="col">
        <label for="aquisicao_dados" class="form-label">Data de Aquisição:</label>
        <input type="date" class="form-control" name="aquisicao_dados" id="aquisicao_dados" value="{{ $viatura->aquisicao_dados ?? old('aquisicao_dados') }}" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
    <div class="col">
        <label for="entrega_dados_opm" class="form-label">Data de Entrega na OPM:</label>
        <input type="date" class="form-control" name="entrega_dados_opm" id="entrega_dados_opm" value="{{ $viatura->entrega_dados_opm ?? old('entrega_dados_opm') }}" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
    </div>
</div>

<!-- Observação -->
<div class="mb-3">
    <label for="observacao" class="form-label">Observação:</label>
    <textarea class="form-control" name="observacao" id="observacao" style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">{{ $viatura->observacao ?? old('observacao') }}</textarea>
</div>
