{{-- resources/views/admin/viaturas/_form.blade.php --}}
@php
    /**
     * Partial de formulário para create/edit.
     * Espera receber:
     * - $veiculo (Model/objeto) ou null
     * - (opcional) $opms (Collection) para select OPM
     * - (opcional) $municipios (Collection) para select município
     * - (opcional) $cidades (Collection<string>) select cidade (da tabela veiculos)
     * - (opcional) $areas (Collection<string>) select area (da tabela veiculos)
     */

    $val = function(string $field, $default = null) use ($veiculo) {
        return old($field, $veiculo?->{$field} ?? $default);
    };

    $dateVal = function(string $field) use ($val) {
        $v = $val($field);
        if (!$v) return null;
        try {
            return \Illuminate\Support\Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $v;
        }
    };

    $estados = [
        'Ativo',
        'Baixado',
        'Em Proc de descarga',
        'Descarregado',
        'Entregue a COPAT',
        'Devolvido a locadora',
    ];

    // TIPOS (o valor "Outros" não será gravado no tipo_veiculo; será gravado no campo tipo_veiculo_outro)
    $tiposVeiculo = [
        'SUV',
        'Pickup',
        'Moto',
        'Sedan',
        'Hatch',
        'Van',
        'Caminhonete',
        'Camioneta',
        'Ônibus',
        'Micro-Ônibus',
        'Caminhão',
        'Utilitário',
        'Reboque',
        'Outros',
    ];

    // COMBUSTÍVEIS (o valor "Outros" não será gravado no combustivel; será gravado no campo combustivel_outro)
    $combustiveis = [
        'Gasolina',
        'Diesel',
        'Flex',
        'Álcool',
        'Elétrico',
        'Híbrido',
        'GNV',
        'Outros',
    ];

    // Se tiver "detalhe outro" salvo no banco, forçamos o select para "Outros" no edit
    $tipoIsOutros = (string)$val('tipo_veiculo') === '' && (string)$val('tipo_veiculo_outro') !== '';
    $combIsOutros = (string)$val('combustivel') === '' && (string)$val('combustivel_outro') !== '';
@endphp

{{-- =========================
     OBRIGATÓRIOS (NOT NULL)
   ========================= --}}
<div class="col-12">
    <div class="fw-semibold text-primary mb-1"><i class="bi bi-card-text"></i> Campos obrigatórios</div>
    <div class="text-muted small">Obrigatórios conforme a tabela <code>veiculos</code>.</div>
</div>

<div class="col-md-6">
    <label class="form-label">Marca/Modelo (texto base) <span class="text-danger">*</span></label>
    <input type="text" name="marca_modelo"
           class="form-control @error('marca_modelo') is-invalid @enderror"
           value="{{ $val('marca_modelo') }}"
           maxlength="255" required>
    @error('marca_modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Placa <span class="text-danger">*</span></label>
    <input type="text" name="placa" id="placa"
           class="form-control @error('placa') is-invalid @enderror"
           value="{{ $val('placa') }}"
           maxlength="7" required autocomplete="off"
           placeholder="Ex.: ABC1D23">
    @error('placa') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Prefixo <span class="text-danger">*</span></label>
    <input type="text" name="prefixo" id="prefixo"
           class="form-control @error('prefixo') is-invalid @enderror"
           value="{{ $val('prefixo') }}"
           maxlength="255" required autocomplete="off">
    @error('prefixo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Chassi</label>
    <input type="text" name="chassi" id="chassi"
           class="form-control @error('chassi') is-invalid @enderror"
           value="{{ $val('chassi') }}" maxlength="17" autocomplete="off">
    @error('chassi') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Renavam</label>
    <input type="text" name="renavam" id="renavam"
           class="form-control @error('renavam') is-invalid @enderror"
           value="{{ $val('renavam') }}" maxlength="11" autocomplete="off">
    @error('renavam') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Nº série do rádio</label>
    <input type="text" name="numero_serie_radio" id="numero_serie_radio"
           class="form-control @error('numero_serie_radio') is-invalid @enderror"
           value="{{ $val('numero_serie_radio') }}" maxlength="100">
    @error('numero_serie_radio') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Cidade/Município <span class="text-danger">*</span></label>
    <select name="cidade" class="form-select @error('cidade') is-invalid @enderror" required>
        <option value="">Selecione...</option>
        @isset($cidades)
            @foreach($cidades as $c)
                <option value="{{ $c }}" @selected((string)$val('cidade') === (string)$c)>{{ $c }}</option>
            @endforeach
        @endisset
    </select>
    @error('cidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Área <span class="text-danger">*</span></label>
    <select name="area" class="form-select @error('area') is-invalid @enderror" required>
        <option value="">Selecione...</option>
        @isset($areas)
            @foreach($areas as $a)
                <option value="{{ $a }}" @selected((string)$val('area') === (string)$a)>{{ $a }}</option>
            @endforeach
        @endisset
    </select>
    @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-4">
    <label class="form-label">OPM <span class="text-danger">*</span></label>

    @isset($opms)
        <select name="opm_id" class="form-select @error('opm_id') is-invalid @enderror" required>
            <option value="">Selecione...</option>

            @foreach($opms as $opm)
                @php
                    $sigla = $opm->sigla ?? null;
                    $nome  = $opm->nome  ?? null;

                    $label = $sigla ?: ($nome ?: ('OPM #'.$opm->id));
                    if ($sigla && $nome) $label = $sigla . ' — ' . $nome;
                @endphp

                <option value="{{ $opm->id }}" @selected((string)$val('opm_id') === (string)$opm->id)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    @else
        <input type="number" name="opm_id"
               class="form-control @error('opm_id') is-invalid @enderror"
               value="{{ $val('opm_id') }}" required min="1">
        <div class="form-text">Dica: passe <code>$opms</code> do controller para virar um select.</div>
    @endisset

    @error('opm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- =========================
     INFORMAÇÕES GERAIS
   ========================= --}}
<div class="col-12 mt-2">
    <div class="fw-semibold text-primary mb-1"><i class="bi bi-gear"></i> Informações gerais</div>
</div>

<div class="col-md-3">
    <label class="form-label">Ano fabricação</label>
    <input type="number" name="ano_fabricacao"
           class="form-control @error('ano_fabricacao') is-invalid @enderror"
           value="{{ $val('ano_fabricacao') }}" min="1900" max="2100">
    @error('ano_fabricacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Estado</label>
    <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="">Selecione...</option>
        @foreach($estados as $e)
            <option value="{{ $e }}" @selected((string)$val('status') === (string)$e)>{{ $e }}</option>
        @endforeach
    </select>
    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- TIPO VEÍCULO (SELECT + OUTROS) --}}
<div class="col-md-3">
    <label class="form-label">Tipo veículo</label>
    <select name="tipo_veiculo" id="tipo_veiculo" class="form-select @error('tipo_veiculo') is-invalid @enderror">
        <option value="">Selecione...</option>
        @foreach($tiposVeiculo as $t)
            @php
                $selected = false;
                if ($t === 'Outros') {
                    $selected = $tipoIsOutros || (string)$val('tipo_veiculo') === 'Outros';
                } else {
                    $selected = (string)$val('tipo_veiculo') === (string)$t;
                }
            @endphp
            <option value="{{ $t }}" @selected($selected)>{{ $t }}</option>
        @endforeach
    </select>
    @error('tipo_veiculo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3" id="wrap_tipo_outro" style="display:none;">
    <label class="form-label">Qual tipo?</label>
    <input type="text" name="tipo_veiculo_outro" id="tipo_veiculo_outro"
           class="form-control @error('tipo_veiculo_outro') is-invalid @enderror"
           value="{{ $val('tipo_veiculo_outro') }}" maxlength="255">
    @error('tipo_veiculo_outro') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Tração</label>
    <input type="text" name="tracao"
           class="form-control @error('tracao') is-invalid @enderror"
           value="{{ $val('tracao') }}" maxlength="255">
    @error('tracao') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- COMBUSTÍVEL (SELECT + OUTROS + ÁLCOOL) --}}
<div class="col-md-3">
    <label class="form-label">Combustível</label>
    <select name="combustivel" id="combustivel" class="form-select @error('combustivel') is-invalid @enderror">
        <option value="">Selecione...</option>
        @foreach($combustiveis as $c)
            @php
                $selected = false;
                if ($c === 'Outros') {
                    $selected = $combIsOutros || (string)$val('combustivel') === 'Outros';
                } else {
                    $selected = (string)$val('combustivel') === (string)$c;
                }
            @endphp
            <option value="{{ $c }}" @selected($selected)>{{ $c }}</option>
        @endforeach
    </select>
    @error('combustivel') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3" id="wrap_comb_outro" style="display:none;">
    <label class="form-label">Qual combustível?</label>
    <input type="text" name="combustivel_outro" id="combustivel_outro"
           class="form-control @error('combustivel_outro') is-invalid @enderror"
           value="{{ $val('combustivel_outro') }}" maxlength="255">
    @error('combustivel_outro') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Categoria</label>
    <input type="text" name="categoria"
           class="form-control @error('categoria') is-invalid @enderror"
           value="{{ $val('categoria') }}" maxlength="255">
    @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Emprego</label>
    <input type="text" name="emprego"
           class="form-control @error('emprego') is-invalid @enderror"
           value="{{ $val('emprego') }}" maxlength="255">
    @error('emprego') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Layout</label>
    <input type="text" name="layout"
           class="form-control @error('layout') is-invalid @enderror"
           value="{{ $val('layout') }}" maxlength="255">
    @error('layout') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- =========================
     CICLO DE VIDA DA VIATURA
   ========================= --}}
<div class="col-12 mt-2">
    <div class="fw-semibold text-primary mb-1">
        <i class="bi bi-calendar-check"></i> Ciclo de vida da viatura
    </div>
</div>

<div class="col-md-3">
    <label class="form-label">Aquisição da viatura</label>
    <input type="date" name="aquisicao_dados"
           class="form-control @error('aquisicao_dados') is-invalid @enderror"
           value="{{ $dateVal('aquisicao_dados') }}">
    @error('aquisicao_dados') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Entrega da viatura à OPM</label>
    <input type="date" name="entrega_dados_opm"
           class="form-control @error('entrega_dados_opm') is-invalid @enderror"
           value="{{ $dateVal('entrega_dados_opm') }}">
    @error('entrega_dados_opm') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- =========================
     BATERIA / GARANTIA
   ========================= --}}
<div class="col-12 mt-2">
    <div class="fw-semibold text-primary mb-1">
        <i class="bi bi-battery-charging"></i> Bateria / Garantia
    </div>
</div>

<div class="col-md-3">
    <label class="form-label">Data inicial da garantia da bateria</label>
    <input type="date" name="dt_inicial_garantia" id="dt_inicial_garantia"
           class="form-control @error('dt_inicial_garantia') is-invalid @enderror"
           value="{{ $dateVal('dt_inicial_garantia') }}">
    @error('dt_inicial_garantia') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">
        Prazo da garantia (meses)
        <span class="text-danger" id="req_meses" style="display:none">*</span>
    </label>
    <input type="number" name="garantia_bateria_meses" id="garantia_bateria_meses"
           class="form-control @error('garantia_bateria_meses') is-invalid @enderror"
           value="{{ $val('garantia_bateria_meses') }}"
           min="1" max="120">
    @error('garantia_bateria_meses') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-3">
    <label class="form-label">Vencimento da garantia (calculado)</label>
    <input type="date" name="dt_final_garantia" id="dt_final_garantia"
           class="form-control @error('dt_final_garantia') is-invalid @enderror"
           value="{{ $dateVal('dt_final_garantia') }}" readonly>
    @error('dt_final_garantia') <div class="invalid-feedback">{{ $message }}</div> @enderror

    <div class="form-text" id="status_garantia" style="display:none"></div>
</div>

<div class="col-md-3">
    <label class="form-label">Nº de série da bateria</label>
    <input type="text" name="n_serie_bateria"
           class="form-control @error('n_serie_bateria') is-invalid @enderror"
           value="{{ $val('n_serie_bateria') }}"
           maxlength="80" autocomplete="off">
    @error('n_serie_bateria') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- =========================
     CONTRATO / PROPRIEDADE
   ========================= --}}
<div class="col-12 mt-2">
    <div class="fw-semibold text-primary mb-1"><i class="bi bi-tags"></i> Contrato / Propriedade</div>
</div>

<div class="col-md-6">
    <label class="form-label">Nº Processo SEI</label>
    <input type="text" name="processo_sei"
           class="form-control @error('processo_sei') is-invalid @enderror"
           value="{{ $val('processo_sei') }}" maxlength="255">
    @error('processo_sei') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label class="form-label">Proprietário</label>
    <input type="text" name="proprietario"
           class="form-control @error('proprietario') is-invalid @enderror"
           value="{{ $val('proprietario') }}" maxlength="255">
    @error('proprietario') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label class="form-label">Contrato</label>
    <input type="text" name="contrato"
           class="form-control @error('contrato') is-invalid @enderror"
           value="{{ $val('contrato') }}" maxlength="255">
    @error('contrato') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-12">
    <label class="form-label">Situação da carga</label>
    <input type="text" name="situacao_carga"
           class="form-control @error('situacao_carga') is-invalid @enderror"
           value="{{ $val('situacao_carga') }}" maxlength="255">
    @error('situacao_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- =========================
     OBSERVAÇÃO
   ========================= --}}
<div class="col-12">
    <label class="form-label">Observação</label>
    <textarea name="observacao" rows="3"
              class="form-control @error('observacao') is-invalid @enderror">{{ $val('observacao') }}</textarea>
    @error('observacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@section('scripts')
<script>
    // Normalizações simples (upper + alfanumérico)
    const upperAlnum = (id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    };

    upperAlnum('placa');
    upperAlnum('prefixo');
    upperAlnum('chassi');
    upperAlnum('renavam');

    // Rádio: se quiser normalizar (opcional), mantenha alfanumérico
    // upperAlnum('numero_serie_radio');

    // ---------- Helpers data ----------
    function addMonthsNoOverflow(dateObj, months) {
        const d = new Date(dateObj.getFullYear(), dateObj.getMonth(), 1);
        d.setMonth(d.getMonth() + months);
        const lastDay = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        const day = Math.min(dateObj.getDate(), lastDay);
        return new Date(d.getFullYear(), d.getMonth(), day);
    }

    function toISODate(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // -------- Tipo "Outros" --------
    const tipoSel = document.getElementById('tipo_veiculo');
    const wrapTipoOutro = document.getElementById('wrap_tipo_outro');
    const tipoOutro = document.getElementById('tipo_veiculo_outro');

    function updateTipoOutro() {
        const isOutros = (tipoSel?.value || '') === 'Outros';
        if (!wrapTipoOutro || !tipoOutro) return;

        wrapTipoOutro.style.display = isOutros ? 'block' : 'none';
        tipoOutro.required = isOutros;

        if (!isOutros) tipoOutro.value = '';
    }

    // -------- Combustível "Outros" --------
    const combSel = document.getElementById('combustivel');
    const wrapCombOutro = document.getElementById('wrap_comb_outro');
    const combOutro = document.getElementById('combustivel_outro');

    function updateCombOutro() {
        const isOutros = (combSel?.value || '') === 'Outros';
        if (!wrapCombOutro || !combOutro) return;

        wrapCombOutro.style.display = isOutros ? 'block' : 'none';
        combOutro.required = isOutros;

        if (!isOutros) combOutro.value = '';
    }

    tipoSel?.addEventListener('change', updateTipoOutro);
    combSel?.addEventListener('change', updateCombOutro);
    updateTipoOutro();
    updateCombOutro();

    // -------- Garantia bateria (prazo em meses) --------
    const dtIni = document.getElementById('dt_inicial_garantia');
    const meses = document.getElementById('garantia_bateria_meses');
    const dtFim = document.getElementById('dt_final_garantia');
    const reqMeses = document.getElementById('req_meses');
    const statusEl = document.getElementById('status_garantia');

    function updateGarantia() {
        if (!dtIni || !meses || !dtFim || !reqMeses || !statusEl) return;

        const iniVal = dtIni.value || '';
        const mesesVal = parseInt(meses.value || '0', 10);

        if (!iniVal) {
            meses.required = false;
            reqMeses.style.display = 'none';
            dtFim.value = '';
            statusEl.style.display = 'none';
            statusEl.innerHTML = '';
            return;
        }

        meses.required = true;
        reqMeses.style.display = 'inline';

        if (!mesesVal || mesesVal < 1) {
            dtFim.value = '';
            statusEl.style.display = 'none';
            statusEl.innerHTML = '';
            return;
        }

        const iniDate = new Date(iniVal + 'T00:00:00');
        const fimDate = addMonthsNoOverflow(iniDate, mesesVal);
        dtFim.value = toISODate(fimDate);

        const hoje = new Date(); hoje.setHours(0,0,0,0);
        const fim = new Date(dtFim.value + 'T00:00:00');
        const venceu = fim < hoje;

        statusEl.style.display = 'block';
        statusEl.innerHTML = venceu
            ? `<span class="badge text-bg-danger">Garantia vencida</span> (venc.: ${dtFim.value})`
            : `<span class="badge text-bg-success">Garantia vigente</span> (até: ${dtFim.value})`;
    }

    dtIni?.addEventListener('change', updateGarantia);
    meses?.addEventListener('input', updateGarantia);
    updateGarantia();
</script>
@endsection
