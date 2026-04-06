@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark text-center">
                    <h3 class="mb-0">🚧 Funcionalidade em Construção</h3>
                </div>

                <div class="card-body text-center">
                    <p class="mb-4">
                        Esta funcionalidade ainda está sendo desenvolvida pela DTIC.<br>
                        Em breve estará disponível para uso.
                    </p>

                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        ⬅ Voltar
                    </a>

                    <a href="{{ route('home') }}" class="btn btn-primary ms-2">
                        Ir para o início
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection