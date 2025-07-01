@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Manutenção</h2>

    <form id="form-manutencao" action="{{ route('admin.manutencoes.store') }}" method="POST">
        @csrf

        @include('admin.manutencoes.partials.form', ['manutencao' => null])

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.manutencoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
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



