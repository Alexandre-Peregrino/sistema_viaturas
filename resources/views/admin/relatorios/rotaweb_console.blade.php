{{-- resources/views/admin/relatorios/rotaweb_console.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-terminal-fill me-2"></i>Console RotaWeb (DEV)
            </h4>
        </div>
        <div class="card-body">

            {{-- Config da chamada --}}
            <div class="mb-4 p-3 border rounded bg-light">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">API Base</label>
                        <input id="apiBase" type="text" class="form-control"
                               value="{{ url('/api/rotaweb') }}" />
                        <div class="form-text">Base das chamadas. Ex.: http://127.0.0.1:8000/api/rotaweb</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">X-API-Key</label>
                        <input id="apiKey" type="password" class="form-control" placeholder="Cole a sua chave aqui">
                        <div class="form-text text-danger">
                            Não deixe esta chave exposta em produção. Use apenas em DEV.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ações rápidas --}}
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-success" id="btnHealth">
                        <i class="bi bi-heart-pulse"></i> Health
                    </button>
                    <button class="btn btn-outline-secondary" id="btnUsuario">
                        <i class="bi bi-person-badge"></i> Usuário
                    </button>
                    <button class="btn btn-outline-secondary" id="btnUnidades">
                        <i class="bi bi-building"></i> Unidades
                    </button>
                    <button class="btn btn-outline-dark" id="btnLimpar">
                        <i class="bi bi-eraser"></i> Limpar saída
                    </button>
                </div>
            </div>

            <hr>

            {{-- EFETIVO GERAL --}}
            <h5 class="mt-3">Efetivo (Geral – sem operação)</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <input class="form-control" id="geralInicio" type="date" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <input class="form-control" id="geralTermino" type="date" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button class="btn btn-outline-primary" id="btnPrevistoGeral">
                        Previsto
                    </button>
                    <button class="btn btn-outline-primary" id="btnPrevistoGeralVtr">
                        Previsto (com VTR)
                    </button>
                    <button class="btn btn-outline-warning" id="btnExecutadoGeral">
                        Executado
                    </button>
                    <button class="btn btn-outline-warning" id="btnExecutadoGeralVtr">
                        Executado (com VTR)
                    </button>
                </div>
            </div>

            <hr>

            {{-- EFETIVO POR OPERAÇÃO --}}
            <h5 class="mt-3">Efetivo por Operação</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <input class="form-control" id="opId" type="text" placeholder="id_operacao (ex: 123)">
                </div>
                <div class="col-md-3">
                    <input class="form-control" id="opInicio" type="date" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <input class="form-control" id="opTermino" type="date" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary" id="btnPrevistoOp">Previsto</button>
                    <button class="btn btn-outline-warning" id="btnExecutadoOp">Executado</button>
                </div>
            </div>

            <hr>

            {{-- FUNÇÕES AUTOFISCALIZÁVEIS / EXECUÇÕES --}}
            <h5 class="mt-3">Autofiscalização – Consultas</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">CPF</label>
                    <input class="form-control" id="afCpf" type="text" placeholder="00000000000" value="">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Início</label>
                    <input class="form-control" id="afInicio" type="datetime-local">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Término</label>
                    <input class="form-control" id="afTermino" type="datetime-local">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-secondary" id="btnFuncoesAf">
                        Funções por CPF/Período
                    </button>
                    <button class="btn btn-outline-secondary" id="btnExecucoesAf">
                        Execuções por CPF/Período
                    </button>
                </div>
            </div>

            <div class="row g-3 align-items-end mt-2">
                <div class="col-md-3">
                    <label class="form-label">Código Função</label>
                    <input class="form-control" id="afCodFuncao" type="text" placeholder="ex: 630970">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Latitude</label>
                    <input class="form-control" id="afLat" type="number" step="0.000001" placeholder="-5.81">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Longitude</label>
                    <input class="form-control" id="afLon" type="number" step="0.000001" placeholder="-35.21">
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-outline-success" id="btnAfInicio">
                        Autofiscalizar (início)
                    </button>

                    <input class="form-control" id="afCodExec" type="text" placeholder="código execução p/ término">
                    <button class="btn btn-outline-danger" id="btnAfTermino">
                        Autofiscalizar (término)
                    </button>
                </div>
            </div>

            <hr>

            {{-- ESCALAS – por agente (lista de CPFs) --}}
            <h5 class="mt-3">Escalas por Agente</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data</label>
                    <input class="form-control" id="eaData" type="date" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CPFs (separe por vírgula)</label>
                    <input class="form-control" id="eaCpfs" type="text" placeholder="00000000000,11111111111">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" id="btnEscalasAgente">
                        Consultar
                    </button>
                </div>
            </div>

            <hr>

            {{-- ESCALAS – últimos N serviços (por matrícula) --}}
            <h5 class="mt-3">Últimas N Escalas (Previstas / Executadas)</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Órgão</label>
                    <input class="form-control" id="ueOrgao" type="number" value="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nº serviços</label>
                    <input class="form-control" id="ueN" type="number" value="5" min="1">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Matrículas (separe por vírgula)</label>
                    <input class="form-control" id="ueMats" type="text" placeholder="1234567,7654321">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary w-50" id="btnPrevistasN">Previstas</button>
                    <button class="btn btn-outline-warning w-50" id="btnExecutadasN">Executadas</button>
                </div>
            </div>

            <hr>

            {{-- Saída --}}
            <h5 class="mt-3">Saída</h5>
            <pre id="output" class="bg-dark text-white p-3 rounded" style="min-height: 240px; overflow:auto; font-size: 0.9rem;"></pre>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
    const $ = (sel) => document.querySelector(sel);
    const out = $('#output');
    const baseEl = $('#apiBase');
    const keyEl  = $('#apiKey');

    function print(obj) {
        out.textContent = (out.textContent ? out.textContent + "\n\n" : "") +
            JSON.stringify(obj, null, 2);
        out.scrollTop = out.scrollHeight;
    }
    function clearOut(){ out.textContent = ''; }

    async function call(method, path, payload=null) {
        const base = baseEl.value.trim();
        const key  = keyEl.value.trim();

        if (!base)  return print({error:'Base vazia'});
        if (!key)   return print({error:'X-API-Key não informada'});

        const url = base.replace(/\/+$/,'') + path;
        const opt = {
            method,
            headers: {
                'X-API-Key': key,
                'Accept': 'application/json'
            }
        };
        if (payload && method !== 'GET') {
            opt.headers['Content-Type'] = 'application/json';
            opt.body = JSON.stringify(payload);
        }

        try {
            const res = await fetch(url, opt);
            const ct = res.headers.get('content-type') || '';
            let data;
            if (ct.includes('application/json')) {
                data = await res.json();
            } else {
                data = { status: res.status, text: await res.text() };
            }
            print({request:{url, method, payload}, response:data, status: res.status});
        } catch (e) {
            print({request:{url, method, payload}, error: String(e)});
        }
    }

    // Botões simples
    $('#btnHealth').addEventListener('click', () => call('GET', '/health'));
    $('#btnUsuario').addEventListener('click', () => call('POST', '/usuario', {}));
    $('#btnUnidades').addEventListener('click', () => call('POST', '/unidades', {}));
    $('#btnLimpar').addEventListener('click', clearOut);

    // Efetivo geral
    const getGeralParams = () => ({
        inicio:  $('#geralInicio').value,
        termino: $('#geralTermino').value,
    });
    $('#btnPrevistoGeral').addEventListener('click', () =>
        call('GET', `/efetivo/previsto-geral?inicio=${encodeURIComponent(getGeralParams().inicio)}&termino=${encodeURIComponent(getGeralParams().termino)}`));
    $('#btnPrevistoGeralVtr').addEventListener('click', () =>
        call('GET', `/efetivo/previsto-geral-com-vtr?inicio=${encodeURIComponent(getGeralParams().inicio)}&termino=${encodeURIComponent(getGeralParams().termino)}`));
    $('#btnExecutadoGeral').addEventListener('click', () =>
        call('GET', `/efetivo/executado-geral?inicio=${encodeURIComponent(getGeralParams().inicio)}&termino=${encodeURIComponent(getGeralParams().termino)}`));
    $('#btnExecutadoGeralVtr').addEventListener('click', () =>
        call('GET', `/efetivo/executado-geral-com-vtr?inicio=${encodeURIComponent(getGeralParams().inicio)}&termino=${encodeURIComponent(getGeralParams().termino)}`));

    // Efetivo por operação
    $('#btnPrevistoOp').addEventListener('click', () => {
        const id = $('#opId').value.trim();
        const i  = $('#opInicio').value;
        const t  = $('#opTermino').value;
        if (!id) return print({error:'Informe id_operacao'});
        call('GET', `/efetivo/previsto?operacao=${encodeURIComponent(id)}&inicio=${encodeURIComponent(i)}&termino=${encodeURIComponent(t)}`);
    });
    $('#btnExecutadoOp').addEventListener('click', () => {
        const id = $('#opId').value.trim();
        const i  = $('#opInicio').value;
        const t  = $('#opTermino').value;
        if (!id) return print({error:'Informe id_operacao'});
        call('GET', `/efetivo/executado?operacao=${encodeURIComponent(id)}&inicio=${encodeURIComponent(i)}&termino=${encodeURIComponent(t)}`);
    });

    // Autofiscalização – consultas e ações
    $('#btnFuncoesAf').addEventListener('click', () => {
        const cpf = $('#afCpf').value.trim();
        const i   = $('#afInicio').value;
        const t   = $('#afTermino').value;
        if (!cpf || !i || !t) return print({error:'CPF, início e término são obrigatórios'});
        call('POST', '/funcoes/autofiscalizaveis', { cpf, inicio: i, termino: t });
    });
    $('#btnExecucoesAf').addEventListener('click', () => {
        const cpf = $('#afCpf').value.trim();
        const i   = $('#afInicio').value;
        const t   = $('#afTermimo').value || $('#afTermino').value; // typo-proof
        if (!cpf || !i || !t) return print({error:'CPF, início e término são obrigatórios'});
        call('POST', '/execucoes/autofiscalizaveis', { cpf, inicio: i, termino: t });
    });
    $('#btnAfInicio').addEventListener('click', () => {
        const cpf = $('#afCpf').value.trim();
        const i   = $('#afInicio').value;
        const t   = $('#afTermino').value;
        const codigo_funcao = $('#afCodFuncao').value.trim();
        const latitude  = parseFloat($('#afLat').value);
        const longitude = parseFloat($('#afLon').value);
        if (!cpf || !i || !t || !codigo_funcao || Number.isNaN(latitude) || Number.isNaN(longitude)) {
            return print({error:'Informe CPF, datas, código função, latitude e longitude'});
        }
        call('POST', '/funcoes/autofiscalizar', { cpf, codigo_funcao, inicio: i, termino: t, latitude, longitude });
    });
    $('#btnAfTermino').addEventListener('click', () => {
        const cpf = $('#afCpf').value.trim();
        const i   = $('#afInicio').value;
        const t   = $('#afTermino').value;
        const codigo_execucao = $('#afCodExec').value.trim();
        if (!cpf || !i || !t || !codigo_execucao) {
            return print({error:'Informe CPF, datas e código_execucao'});
        }
        call('POST', '/autofiscalizar/termino', { cpf, inicio: i, termino: t, codigo_execucao });
    });

    // Escalas por agente
    $('#btnEscalasAgente').addEventListener('click', () => {
        const data = $('#eaData').value;
        const cpfs = $('#eaCpfs').value.trim();
        if (!data || !cpfs) return print({error:'Informe data e CPFs'});
        const cpfsArr = cpfs.split(',').map(s => s.trim()).filter(Boolean);
        call('POST', '/escalas/agente', { data, cpfs: cpfsArr });
    });

    // Últimas N escalas (por matrícula)
    $('#btnPrevistasN').addEventListener('click', () => {
        const orgao = parseInt($('#ueOrgao').value, 10);
        const n     = parseInt($('#ueN').value, 10);
        const mats  = ($('#ueMats').value || '').split(',').map(s => s.trim()).filter(Boolean);
        if (!orgao || !n || mats.length===0) return print({error:'Informe orgão, n e pelo menos uma matrícula'});
        call('POST', '/escalas/previstas', { orgao, n, matriculas: mats });
    });
    $('#btnExecutadasN').addEventListener('click', () => {
        const orgao = parseInt($('#ueOrgao').value, 10);
        const n     = parseInt($('#ueN').value, 10);
        const mats  = ($('#ueMats').value || '').split(',').map(s => s.trim()).filter(Boolean);
        if (!orgao || !n || mats.length===0) return print({error:'Informe orgão, n e pelo menos uma matrícula'});
        call('POST', '/escalas/executadas', { orgao, n, matriculas: mats });
    });

})();
</script>
@endsection
