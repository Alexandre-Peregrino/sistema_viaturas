{{-- resources/views/admin/viaturas/_form.blade.php --}}
@php
    /**
     * Partial de formulário para create/edit.
     * Espera receber:
     * - $veiculo (Model/objeto) ou null
     * - (opcional) $areas (Collection<string>) (pode vir vazio; JS pode carregar)
     *
     * OBS: Município agora é gravado em veiculos.municipio_id (FK).
     *      Evitar duplicação: não expomos veiculos.cidade aqui; derive no backend.
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

    $tipoIsOutros = (string)$val('tipo_veiculo') === '' && (string)$val('tipo_veiculo_outro') !== '';
    $combIsOutros = (string)$val('combustivel') === '' && (string)$val('combustivel_outro') !== '';

    $isEdit = !empty($veiculo) && !empty($veiculo->id);

    $lotAtual = null;
    try {
        if ($isEdit) {
            if (method_exists($veiculo, 'lotacaoAtual')) {
                $lotAtual = $veiculo->lotacaoAtual ?? null;
            }
            if (!$lotAtual && isset($veiculo->lotacoes)) {
                $lotAtual = $veiculo->lotacoes->firstWhere('data_saida', null);
            }
        }
    } catch (\Throwable $e) {
        $lotAtual = null;
    }

    $lotOpmSigla = $lotAtual?->opm?->sigla ?? null;
    $lotOpmNome  = $lotAtual?->opm?->nome ?? null;
    $lotEntrada  = $lotAtual?->data_entrada ? \Illuminate\Support\Carbon::parse($lotAtual->data_entrada)->format('d/m/Y') : null;

    // valores selecionados (edit / old)
    $selectedCpr         = (string) $val('area', '');
    $selectedMunicipioId = (string) $val('municipio_id', '');
    $selectedOpmId       = (string) $val('opm_id', '');
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
    <label class="form-label">ID Rádio</label>
    <input type="text" name="numero_serie_radio" id="numero_serie_radio"
           class="form-control @error('numero_serie_radio') is-invalid @enderror"
           value="{{ $val('numero_serie_radio') }}" maxlength="100">
    @error('numero_serie_radio') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- CPR (topo) --}}
<div class="col-md-4">
    <label class="form-label">CPR (Área) <span class="text-danger">*</span></label>
    <select name="area" id="area" data-selected="{{ $selectedCpr }}"
            class="form-select @error('area') is-invalid @enderror" required>
        <option value="">Selecione...</option>
        @isset($areas)
            @foreach($areas as $a)
                <option value="{{ $a }}" @selected((string)$selectedCpr === (string)$a)>{{ $a }}</option>
            @endforeach
        @endisset
    </select>
    @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">O CPR define a lista de OPMs e, por OPM, os municípios de cobertura.</div>
</div>

{{-- ✅ Movimentações (lotação) + OPM --}}
<div class="col-md-4">
    @if($isEdit)
        <div class="alert alert-info py-2 mb-2">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-clock-history"></i>
                <div>
                    <div class="fw-semibold">Movimentações (lotação)</div>

                    @if($lotAtual)
                        <div class="small">
                            Lotação atual:
                            <strong>{{ $lotOpmSigla ?? ('OPM #'.$lotAtual->opm_id) }}</strong>
                            @if($lotOpmNome) <span class="text-muted">— {{ $lotOpmNome }}</span> @endif
                            @if($lotEntrada) <span class="text-muted">(desde {{ $lotEntrada }})</span> @endif
                        </div>

                        <div class="small text-muted">
                            Ao alterar a OPM e salvar, o sistema fecha a lotação atual e cria uma nova movimentação automaticamente.
                        </div>
                    @else
                        <div class="small"><strong>Sem lotação aberta registrada.</strong></div>
                        <div class="small text-muted">
                            Ao salvar, o sistema criará uma lotação inicial automaticamente (regularização).
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <label class="form-label">OPM <span class="text-danger">*</span></label>
    <select name="opm_id" id="opm_id" data-selected="{{ $selectedOpmId }}"
            class="form-select @error('opm_id') is-invalid @enderror" required>
        <option value="">Selecione...</option>
    </select>
    @error('opm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- Município (FK) --}}
<div class="col-md-4">
    <label class="form-label">Município <span class="text-danger">*</span></label>
    <select name="municipio_id" id="municipio_id" data-selected="{{ $selectedMunicipioId ?? '' }}"
            class="form-select @error('municipio_id') is-invalid @enderror" required>
        
        @if(isset($veiculo) && $veiculo->municipio)
            {{-- Mantém a cidade atual selecionada ao carregar a página de edição --}}
            <option value="{{ $veiculo->municipio_id }}" selected>{{ $veiculo->municipio->nome }}</option>
        @else
            <option value="">Selecione a OPM primeiro...</option>
        @endif

    </select>
    @error('municipio_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Municípios carregados conforme cobertura da OPM.</div>
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

@push('scripts')
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

    // ================================
    // CPR -> OPM -> Municípios (cobertura real com herança ADM)
    // ================================
    (function () {
        const selCpr       = document.getElementById('area');
        const selOpm       = document.getElementById('opm_id');
        const selMunicipio = document.getElementById('municipio_id');

        if (!selCpr || !selOpm || !selMunicipio) return;

        const urlCprs = @json(route('admin.ajax.cprs'));
        const urlOpms = @json(route('admin.ajax.opms_por_cpr'));
        const urlMunicipiosPorOpm = @json(route('admin.ajax.municipios_por_opm'));

        const selectedCpr = selCpr.dataset.selected || '';
        const selectedOpmId = selOpm.dataset.selected || '';
        const selectedMunicipioId = selMunicipio.dataset.selected || '';

        function setLoading(selectEl, loading = true) {
            if (!selectEl) return;
            selectEl.disabled = loading;
            selectEl.classList.toggle('opacity-75', loading);
        }

        function resetSelect(selectEl, placeholder = 'Selecione...') {
            if (!selectEl) return;
            selectEl.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            selectEl.appendChild(opt);
        }

        async function fetchJson(url) {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json();
        }

        async function loadCprs() {
            setLoading(selCpr, true);
            resetSelect(selCpr);

            try {
                const cprs = await fetchJson(urlCprs);
                cprs.forEach((cpr) => {
                    const opt = document.createElement('option');
                    opt.value = cpr;
                    opt.textContent = cpr;
                    selCpr.appendChild(opt);
                });

                if (selectedCpr) selCpr.value = selectedCpr;
            } catch (e) {
                console.error('Falha ao carregar CPRs', e);
            } finally {
                setLoading(selCpr, false);
            }
        }

        async function loadOpms(cpr) {
            setLoading(selOpm, true);
            resetSelect(selOpm);

            resetSelect(selMunicipio, 'Selecione a OPM primeiro...');
            selMunicipio.disabled = true;

            try {
                if (!cpr) return;

                const opms = await fetchJson(urlOpms + '?cpr=' + encodeURIComponent(cpr));
                opms.forEach((o) => {
                    const opt = document.createElement('option');
                    opt.value = String(o.id);
                    opt.textContent = o.label;
                    selOpm.appendChild(opt);
                });

                if (selectedOpmId) selOpm.value = selectedOpmId;
            } catch (e) {
                console.error('Falha ao carregar OPMs por CPR', e);
            } finally {
                setLoading(selOpm, false);
            }
        }

        async function loadMunicipiosByOpm(opmId) {
            setLoading(selMunicipio, true);
            resetSelect(selMunicipio);

            try {
                if (!opmId) {
                    resetSelect(selMunicipio, 'Selecione a OPM primeiro...');
                    selMunicipio.disabled = true;
                    return;
                }

                const municipios = await fetchJson(urlMunicipiosPorOpm + '?opm_id=' + encodeURIComponent(opmId));

                if (municipios.length > 0) {
                    // Tem municípios próprios
                    municipios.forEach((m) => {
                        const opt = document.createElement('option');
                        opt.value = String(m.id);
                        opt.textContent = m.label;
                        selMunicipio.appendChild(opt);
                    });
                } else {
                    // Fallback: Herança do Subcomando Geral (ID 1972)
                    const subData = await fetchJson(urlMunicipiosPorOpm + '?opm_id=1972');
                    if (subData.length > 0) {
                        subData.forEach((m) => {
                            const opt = document.createElement('option');
                            opt.value = String(m.id);
                            opt.textContent = m.label + ' (ADM - Subcomando)';
                            selMunicipio.appendChild(opt);
                        });
                    } else {
                        resetSelect(selMunicipio, 'Nenhuma cidade disponível');
                    }
                }

                selMunicipio.disabled = false;

                if (selectedMunicipioId) selMunicipio.value = selectedMunicipioId;
            } catch (e) {
                console.error('Falha ao carregar municípios por OPM', e);
                resetSelect(selMunicipio, 'Erro ao carregar municípios');
                selMunicipio.disabled = true;
            } finally {
                setLoading(selMunicipio, false);
            }
        }

        (async function init() {
            resetSelect(selMunicipio, 'Selecione a OPM primeiro...');
            selMunicipio.disabled = true;

            await loadCprs();
            await loadOpms(selCpr.value);
            await loadMunicipiosByOpm(selOpm.value);
        })();

        selCpr.addEventListener('change', async () => {
            selOpm.dataset.selected = '';
            selMunicipio.dataset.selected = '';
            await loadOpms(selCpr.value);
            await loadMunicipiosByOpm(selOpm.value);
        });

        selOpm.addEventListener('change', async () => {
            selMunicipio.dataset.selected = '';
            await loadMunicipiosByOpm(selOpm.value);
        });
    })();
</script>
@endpush