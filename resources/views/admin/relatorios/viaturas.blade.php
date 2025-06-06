@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Relatório Geral de Viaturas</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Tipo</th>
                <th>OPM</th>
                <th>Status</th>
                <th>Locação</th>
                <th>Manutenções</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($viaturas as $v)
            <tr>
                <td>{{ $v->placa }}</td>
                <td>{{ $v->modelo }}</td>
                <td>{{ $v->tipo }}</td>
                <td>{{ $v->opm->nome ?? 'Não informado' }}</td>
                <td>{{ $v->status }}</td>
                <td>{{ $v->locada ? 'Locada' : 'Própria' }}</td>
                <td>{{ $v->manutencoes->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
