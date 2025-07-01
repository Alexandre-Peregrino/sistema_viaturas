@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Resultados do Relatório</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Placa</th>
                <th>OPM</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Manutenções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($viaturas as $viatura)
                <tr>
                    <td>{{ $viatura->placa }}</td>
                    <td>{{ $viatura->opm->nome }}</td>
                    <td>{{ ucfirst($viatura->status) }}</td>
                    <td>{{ ucfirst($viatura->tipo_aquisicao) }}</td>
                    <td>
                        @foreach($viatura->manutencoes as $manutencao)
                            <div>{{ $manutencao->descricao }} ({{ $manutencao->data_inicio }})</div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
