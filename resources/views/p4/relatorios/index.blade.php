@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Relatórios da Minha OPM</h2>

    <a href="{{ route('p4.relatorios.viaturas') }}" class="btn btn-primary mb-3">Ver Viaturas da OPM</a>
</div>
@endsection
