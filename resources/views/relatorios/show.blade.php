@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Detalhes da Viatura - {{ $viatura->placa }}</h2>

    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Modelo:</strong> {{ $viatura->modelo }}</li>
        <li class="list-group-item"><strong>Tipo:</strong> {{ ucfirst($viatura->tipo) }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($viatura->status) }}</li>
        <li class="list-group-item"><strong>OPM:</strong> {{ $viatura->opm->nome }}</li>
    </ul>

    <h4>Histórico de Manutenções</h4>
    <ul class="list-group mb-3">
        @forelse ($viatura->manutencoes as $m)
            <li class="list-group-item">
                {{ $m->data_inicio }} - {{ $m->descricao }} ({{ $m->tipo }})
            </li>
        @empty
            <li class="list-group-item">Nenhuma manutenção registrada.</li>
        @endforelse
    </ul>

    <h4>Abastecimentos</h4>
    <ul class="list-group">
        @forelse ($viatura->abastecimentos as $a)
            <li class="list-group-item">
                {{ $a->data }} - {{ $a->quantidade }} litros
            </li>
        @empty
            <li class="list-group-item">Nenhum abastecimento registrado.</li>
        @endforelse
    </ul>
</div>
@endsection
