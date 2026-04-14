@extends('layouts.app')

@section('content')
<div class="bg-light bg-opacity-95 p-5 rounded shadow-lg mx-auto max-w-4xl min-vh-75">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <h2 class="text-dark fw-bold mb-4 text-center">Editar Manutenção</h2>

            <form action="{{ route('admin.manutencoes.update', $manutencao->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin.manutencoes.partials.form', ['manutencao' => $manutencao])

                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-4">Atualizar</button>
                    <a href="{{ route('admin.manutencoes.index') }}" class="btn btn-secondary btn-lg px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection