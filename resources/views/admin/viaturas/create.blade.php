@extends('layouts.app')

@section('content')
<div class="container p-4 rounded shadow-sm" style="background-color: #F0F0F0;">
    <h1 class="mb-4 text-primary text-center">Cadastrar Nova Viatura</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.viaturas.store') }}" method="POST">
        @csrf

        {{-- Inclui o formulário parcial, passando uma nova instância de Viatura para preenchimento --}}
        @include('admin.viaturas.partials.form', ['viatura' => new App\Models\Veiculo(), 'opms' => $opms, 'radios' => $radios])

        <!-- Botões de Ação -->
        <div class="d-flex justify-content-between mt-4"> {{-- Adicionado mt-4 para espaçamento --}}
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('admin.viaturas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection