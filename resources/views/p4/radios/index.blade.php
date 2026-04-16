@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Meus Rádios - P4</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nº Série</th>
                <th>Modelo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($radios as $radio)
            <tr>
                <td>{{ $radio->numero_serie }}</td>
                <td>{{ $radio->modelo }}</td>
                <td>{{ $radio->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
