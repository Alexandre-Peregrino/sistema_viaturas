@csrf

<div class="mb-3">
    <label for="numero_serie" class="form-label">Número de Série</label>
    <input type="text" name="numero_serie" class="form-control"
           value="{{ old('numero_serie', $radio->numero_serie ?? '') }}" required
           style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
</div>

<div class="mb-3">
    <label for="marca" class="form-label">Marca</label>
    <input type="text" name="marca" class="form-control"
           value="{{ old('marca', $radio->marca ?? '') }}" required
           style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
</div>

<div class="mb-3">
    <label for="modelo" class="form-label">Modelo</label>
    <input type="text" name="modelo" class="form-control"
           value="{{ old('modelo', $radio->modelo ?? '') }}" required
           style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
</div>

<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <input type="text" name="status" class="form-control"
           value="{{ old('status', $radio->status ?? '') }}" required
           style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
</div>

<div class="mb-3">
    <label for="frequencia" class="form-label">Frequência</label>
    <input type="text" name="frequencia" class="form-control"
           value="{{ old('frequencia', $radio->frequencia ?? '') }}"
           style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
</div>

<div class="mb-3">
    <label for="opm_id" class="form-label">OPM</label>
    <select name="opm_id" class="form-select" required
            style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">
        <option value="">Selecione uma OPM</option>
        @foreach ($opms as $opm)
            <option value="{{ $opm->id }}" {{ (old('opm_id', $radio->opm_id ?? '') == $opm->id) ? 'selected' : '' }}>
                {{ $opm->nome }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="observacao" class="form-label">Observação</label>
    <textarea name="observacao" class="form-control" rows="3"
              style="background-color: #F8F8F8; border: 1px solid #A0A0A0;">{{ old('observacao', $radio->observacao ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">Salvar</button>
