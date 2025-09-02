@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card">
        <div class="card-header text-center bg-primary text-white">
          <h2 class="mb-0">Integrações RotaWeb</h2>
        </div>

        <div class="card-body">
          <p class="lead text-center mb-4">
            Acesse rapidamente os relatórios do RotaWeb diretamente pelo sistema.
          </p>

          @guest
            <div class="d-flex justify-content-center flex-wrap">
              <a href="{{ route('login') }}" class="btn btn-success btn-lg mx-2 mb-2">
                <i class="bi bi-box-arrow-in-right"></i> Fazer Login
              </a>
            </div>
          @else
            <h3 class="text-center mb-4 w-100">Atalhos</h3>
            @if(Auth::user()->isAdmin())
              <div class="d-flex justify-content-center flex-wrap">
                <a href="{{ route('admin.relatorios.rotaweb.efetivo_previsto.filtros') }}"
                   class="btn btn-outline-primary btn-lg mx-2 mb-2">
                  <i class="bi bi-clipboard-data"></i> Efetivo Previsto
                </a>

                <a href="{{ route('admin.relatorios.rotaweb.efetivo_executado.filtros') }}"
                   class="btn btn-outline-success btn-lg mx-2 mb-2">
                  <i class="bi bi-graph-up-arrow"></i> Efetivo Executado
                </a>
              </div>
            @else
              <div class="alert alert-warning text-center">
                Acesso aos relatórios RotaWeb restrito a administradores.
              </div>
            @endif

            <div class="text-center mt-4">
              <a href="{{ route('home') }}" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Voltar para a Home
              </a>
            </div>
          @endguest
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
