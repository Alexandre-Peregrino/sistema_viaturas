@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Manutenção</h2>

    <form action="{{ route('admin.manutencoes.update', $manutencao->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.manutencoes.partials.form', ['manutencao' => $manutencao])

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.manutencoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
