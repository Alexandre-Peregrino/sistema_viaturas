<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Veículo</label>
    <select name="veiculo_id" class="form-control bg-white border-dark border-2 text-dark" required>
        <option value="">Selecione</option>
        @foreach($veiculos as $v)
            <option value="{{ $v->id }}"
                {{ old('veiculo_id', $manutencao->veiculo_id ?? '') == $v->id ? 'selected' : '' }}>
                {{ $v->placa }} - {{ $v->prefixo }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Descrição</label>
    <input type="text" name="descricao" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('descricao', $manutencao->descricao ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Data Início</label>
    <input type="date" name="data_inicio" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('data_inicio', $manutencao->data_inicio ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Data Fim</label>
    <input type="date" name="data_fim" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('data_fim', $manutencao->data_fim ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Tipo</label>
    <select name="tipo" class="form-control bg-white border-dark border-2 text-dark" required>
        <option value="preventiva" {{ old('tipo', $manutencao->tipo ?? '') == 'preventiva' ? 'selected' : '' }}>Preventiva</option>
        <option value="corretiva" {{ old('tipo', $manutencao->tipo ?? '') == 'corretiva' ? 'selected' : '' }}>Corretiva</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Valor</label>
    <input type="number" step="0.01" name="valor" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('valor', $manutencao->valor ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Oficina</label>
    <input type="text" name="oficina" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('oficina', $manutencao->oficina ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label text-dark fw-bold fs-6">Status</label>
    <input type="text" name="status" class="form-control bg-white border-dark border-2 text-dark"
        value="{{ old('status', $manutencao->status ?? 'aberta') }}">
</div>