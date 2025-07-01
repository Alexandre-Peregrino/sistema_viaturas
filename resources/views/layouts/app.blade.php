<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Frota') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Styles for full height sidebar and main content background -->
    <style>
        html, body, #app {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        main {
            flex-grow: 1; /* Permite que o main ocupe o espaço restante */
            display: flex; /* Transforma o main em flex container */
            flex-direction: column; /* Permite que o conteúdo interno se organize em coluna */
        }
        .row.main-content-row { /* Nova classe para a linha principal de conteúdo */
            flex-grow: 1; /* Faz a linha ocupar o espaço restante dentro do main */
        }
    </style>

    <!-- Scripts -->
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{ config('app.name', 'Sistema de Frota') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->nome }} {{-- Usando 'nome' conforme seu modelo de usuário --}}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form-topbar').submit();">
                                        {{ __('Sair') }}
                                    </a>

                                    <form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- main agora é um flex container e ocupa o espaço restante --}}
        <main class="py-4">
            <div class="container-fluid h-100">
                {{-- Adicionado class "main-content-row" e h-100 para a linha principal --}}
                <div class="row main-content-row h-100">
                    @auth
                    {{-- COLUNA DO MENU LATERAL: Adicionadas classes bg-primary e text-white, h-100 e rounded-lg para arredondamento --}}
                    <div class="col-md-3 bg-primary text-white p-0 h-100 rounded-lg">
                        <div class="list-group list-group-flush rounded-0">
                            <a href="{{ route('home') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                <i class="bi bi-house-door-fill"></i> Início
                            </a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.usuarios.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-people-fill"></i> Usuários (todas OPMs)
                                </a>
                                <a href="{{ route('admin.viaturas.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-car-front-fill"></i> Viaturas (todas)
                                </a>
                                {{-- ITEM DE MENU LATERAL PARA RÁDIOS (ADMIN) --}}
                                <a href="{{ route('admin.radios.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-broadcast-pin"></i> Rádios (todas)
                                </a>
                                <a href="{{ route('admin.relatorios.geral') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-clipboard-data-fill"></i> Relatórios (Admin)
                                </a>
                                <a href="{{ route('admin.manutencoes.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-tools"></i> Manutenções (Admin)
                                </a>
                            @elseif(Auth::user()->isP4())
                                <a href="{{ route('p4.viaturas.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-car-front-fill"></i> Minhas Viaturas (P4)
                                </a>
                                <a href="{{ route('p4.manutencoes.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-tools"></i> Minhas Manutenções (P4)
                                </a>
                                <a href="{{ route('p4.radios.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-broadcast-pin"></i> Meus Rádios (P4)
                                </a>
                                <a href="{{ route('p4.relatorios.index') }}" class="list-group-item list-group-item-action bg-transparent text-white">
                                    <i class="bi bi-clipboard-data-fill"></i> Meus Relatórios (P4)
                                </a>
                            @endif
                            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                            {{-- Botão Sair com background danger e texto branco para contraste --}}
                            <a class="list-group-item list-group-item-action bg-danger text-white" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </div>
                    </div>
                    {{-- COLUNA DE CONTEÚDO: Estilo inline para fundo cinza mais escuro e h-100 para altura total --}}
                    <div class="col-md-9 h-100" style="background-color: #f0f0f0; padding-top: 1rem; padding-bottom: 1rem;">
                        @yield('content')
                    </div>
                    @else
                        {{-- Para usuários não logados, a coluna de conteúdo ocupa toda a largura e tem fundo cinza claro --}}
                        <div class="col-md-12" style="background-color: #f0f0f0;">
                            @yield('content')
                        </div>
                    @endauth
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
