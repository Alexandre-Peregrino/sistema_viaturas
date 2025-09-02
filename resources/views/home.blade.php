@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10"> {{-- Aumentado para melhor visualização do conteúdo --}}
            <div class="card">
                <div class="card-header text-center bg-primary text-white"> {{-- Estilo para o cabeçalho --}}
                    <h2 class="mb-0">Bem-vindo(a) ao Sistema de Gestão de Frota</h2>
                </div>

                <div class="card-body">
                    <p class="lead text-center mb-4">
                        Este sistema foi desenvolvido para auxiliar no controle e gerenciamento eficiente de veículos e equipamentos.
                        Monitore viaturas, rádios e usuários, acompanhe manutenções e acesse relatórios detalhados para uma gestão otimizada da sua frota.
                    </p>

                    <hr class="my-4"> {{-- Divisor --}}

                    <div>
                        @guest {{-- Se o usuário NÃO estiver logado --}}
                            <div class="d-flex justify-content-center flex-wrap">
                                <a href="{{ route('login') }}" class="btn btn-success btn-lg mx-2 mb-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Fazer Login
                                </a>
                                <a href="{{ route('funcionalidades') }}" class="btn btn-info btn-lg mx-2 mb-2">
                                    <i class="bi bi-question-circle"></i> Funcionalidades do Sistema
                                </a>
                            </div>
                        @else {{-- Se o usuário ESTIVER logado --}}
                            <h3 class="text-center mb-4 w-100">Painel de Controle</h3>
                            <p class="text-center w-100">Você está logado como
                                @if(Auth::user()->isAdmin())
                                    **Administrador**.
                                @elseif(Auth::user()->isP4())
                                    **P4** (OPM: {{ Auth::user()->opm->sigla ?? 'Não definida' }}).
                                @else
                                    um usuário regular.
                                @endif
                            </p>

                            <div class="mt-4 w-100">
                                <div class="d-flex justify-content-center flex-wrap">
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('admin.viaturas.index') }}" class="btn btn-primary btn-lg mx-2 mb-2">Gerenciar Viaturas</a>
                                        <a href="{{ route('admin.radios.index') }}" class="btn btn-warning btn-lg mx-2 mb-2">Gerenciar Rádios</a>
                                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-info btn-lg mx-2 mb-2">Gerenciar Usuários</a>
                                        <a href="{{ route('admin.relatorios.geral') }}" class="btn btn-secondary btn-lg mx-2 mb-2">Relatórios Gerais</a>
                                    @elseif(Auth::user()->isP4())
                                        <a href="{{ route('p4.viaturas.index') }}" class="btn btn-primary btn-lg mx-2 mb-2">Minhas Viaturas</a>
                                        <a href="{{ route('p4.manutencoes.index') }}" class="btn btn-info btn-lg mx-2 mb-2">Minhas Manutenções</a>
                                        <a href="{{ route('p4.radios.index') }}" class="btn btn-warning btn-lg mx-2 mb-2">Meus Rádios</a>
                                        <a href="{{ route('p4.relatorios.index') }}" class="btn btn-secondary btn-lg mx-2 mb-2">Relatórios</a>
                                    @endif
                                    
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-lg mx-2 mb-2">Sair</button>
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
