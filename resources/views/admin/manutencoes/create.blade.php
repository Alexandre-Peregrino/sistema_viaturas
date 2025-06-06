@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Manutenção</h2>

    <form action="{{ route('admin.manutencoes.store') }}" method="POST">
        @csrf

        @include('admin.manutencoes.partials.form', ['manutencao' => null])

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.manutencoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
