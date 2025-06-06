@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Novo Rádio</h2>

    <form action="{{ route('admin.radios.store') }}" method="POST">
        @include('admin.radios.form')
    </form>
</div>
@endsection
