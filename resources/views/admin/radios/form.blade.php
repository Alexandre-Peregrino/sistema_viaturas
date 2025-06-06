@csrf

<div class="mb-3">
    <label for="numero_serie" class="form-label">Número de Série</label>
    <input type="text" name="numero_serie" class="form-control" value="{{ old('numero_serie', $radio->numero_serie ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="modelo" class="form-label">Modelo</label>
    <input type="text" name="modelo" class="form-control" value="{{ old('modelo', $radio->modelo ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="frequencia" class="form-label">Frequência</label>
    <input type="text" name="frequencia" class="form-control" value="{{ old('frequencia', $radio->frequencia ?? '') }}">
</div>

<div class="mb-3">
    <label for="opm_id" class="form-label">OPM</label>
    <select name="opm_id" class="form-select" required>
        <option value="">Selecione uma OPM</option>
        @foreach ($opms as $opm)
            <option value="{{ $opm->id }}" {{ (old('opm_id', $radio->opm_id ?? '') == $opm->id) ? 'selected' : '' }}>
                {{ $opm->nome }}
            </option>
        @endforeach
    </select>
</div>

<button type="submit" class="btn btn-primary">Salvar</button>
