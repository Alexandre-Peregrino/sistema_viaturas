{{-- resources/views/admin/viaturas/_movimentacoes.blade.php --}}

@php
    $movs = $veiculo->lotacoes ?? collect();
@endphp

@if($movs->isEmpty())
    <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Não há movimentações registradas para esta viatura.
        <div class="small text-muted">
            Ao salvar uma alteração, o sistema pode criar uma lotação inicial automaticamente (regularização).
        </div>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 160px;">Período</th>
                    <th>OPM</th>
                    <th style="width: 180px;">Município</th>
                    <th style="width: 220px;">Motivo</th>
                    <th style="width: 220px;">Usuário</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movs as $m)
                    @php
                        $entrada = $m->data_entrada ? \Illuminate\Support\Carbon::parse($m->data_entrada)->format('d/m/Y') : '-';
                        $saida   = $m->data_saida ? \Illuminate\Support\Carbon::parse($m->data_saida)->format('d/m/Y') : null;
                        $aberta  = is_null($m->data_saida);
                    @endphp

                    <tr>
                        <td>
                            <div class="d-flex flex-column">
                                <div>
                                    <span class="fw-semibold">{{ $entrada }}</span>
                                    <span class="text-muted">→</span>
                                    <span class="{{ $aberta ? 'text-success fw-semibold' : '' }}">
                                        {{ $saida ?? 'Atual' }}
                                    </span>
                                </div>
                                @if($aberta)
                                    <div class="small">
                                        <span class="badge bg-success">Lotação atual</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $m->opm->sigla ?? ('#' . $m->opm_id) }}
                            </div>
                            <div class="small text-muted">
                                {{ $m->opm->nome ?? '' }}
                            </div>
                        </td>

                        <td>
                            {{ $m->municipio->nome ?? '-' }}
                        </td>

                        <td>
                            {{ $m->motivo ?? '-' }}
                        </td>

                        <td>
                            @if(!empty($m->usuario))
                                <div class="fw-semibold">{{ $m->usuario->nome ?? 'Usuário' }}</div>
                                <div class="small text-muted">
                                    {{ $m->usuario->matricula ?? '' }}
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            {{ $m->observacao ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
