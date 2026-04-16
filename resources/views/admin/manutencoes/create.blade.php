@extends('layouts.app')

@section('content')
<div class="bg-light bg-opacity-95 p-5 rounded shadow-lg mx-auto max-w-4xl min-vh-75">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <h2 class="text-dark fw-bold mb-4 text-center">Nova Manutenção</h2>

            <form id="form-manutencao" action="{{ route('admin.manutencoes.store') }}" method="POST">
                @csrf

                @include('admin.manutencoes.partials.form', ['manutencao' => null])

                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-4">Salvar</button>
                    <a href="{{ route('admin.manutencoes.index') }}" class="btn btn-secondary btn-lg px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-manutencao');
        const dataInicio = document.querySelector('input[name="data_inicio"]');
        const dataFim = document.querySelector('input[name="data_fim"]');

        form.addEventListener('submit', function (e) {
            // Remove alertas antigos
            const oldAlert = document.getElementById('alerta-data');
            if (oldAlert) oldAlert.remove();

            if (dataFim.value && dataInicio.value) {
                const inicio = new Date(dataInicio.value);
                const fim = new Date(dataFim.value);

                if (fim < inicio) {
                    e.preventDefault();

                    const alerta = document.createElement('div');
                    alerta.id = 'alerta-data';
                    alerta.className = 'alert alert-danger mt-2';
                    alerta.innerText = 'A data de término não pode ser anterior à data de início.';
                    
                    dataFim.parentNode.appendChild(alerta);
                }
            }
        });
    });
</script>
@endsection