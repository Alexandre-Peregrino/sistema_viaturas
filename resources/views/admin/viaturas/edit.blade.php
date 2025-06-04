<!-- resources/views/admin/viaturas/edit.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Editar Viatura</h1>

    <form action="{{ route('admin.viaturas.update', $viatura->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.viaturas.partials.form', ['viatura' => $viatura])

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="{{ route('admin.viaturas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection