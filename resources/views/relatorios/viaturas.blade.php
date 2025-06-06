@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">{{ $titulo }}</h2>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>OPM</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($viaturas as $viatura)
                <tr>
                    <td>{{ $viatura->placa }}</td>
                    <td>{{ $viatura->modelo }}</td>
                    <td>{{ ucfirst($viatura->tipo) }}</td>
                    <td>{{ ucfirst($viatura->status) }}</td>
                    <td>{{ $viatura->opm->nome }}</td>
                    <td>
                        <a href="{{ route('relatorios.detalhado', $viatura->id) }}" class="btn btn-info btn-sm">Ver Detalhes</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
