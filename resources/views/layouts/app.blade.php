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
    /* Variáveis para light/dark */
    :root {
    --bg-body: #121313;      
    --bg-main: #061a57;      
    --bg-card: #e8ebf0;      
    --bg-input: #f2f4f7;     
    --bg-sidebar: #506487;   
    --text-primary: #000000; 
    --text-secondary: #000000; 
    --border: #151616;       
    }

    [data-theme="dark"] {
    --bg-body: #121212;
    --bg-main: #151515;
    --bg-card: #1f1f1f; /* Cards bem escuros */
    --bg-input: #2d2d2d;
    --bg-sidebar: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
    --text-primary: #2e2b2b; /* Off-white suave, sem agressão */
    --text-secondary: #beb5b5;
    --border: #ffffff;
    }

    html, body, #app {
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: var(--bg-body) !important;
    color: var(--text-primary) !important;
    transition: all 0.3s ease;
    }

    main { flex-grow: 1; display: flex; flex-direction: column; }
    .row.main-content-row { flex-grow: 1; }

    .col-md-2 { background: var(--bg-sidebar) !important; }
    .sidebar-link { color: var(--text-primary) !important; border-color: rgba(255,255,255,0.1) !important; }
    .sidebar-link.active { background-color: rgba(255,255,255,0.15) !important; font-weight: 600; }
    .sidebar-link:hover { background-color: rgba(255,255,255,0.25) !important; }

    .main-pane {
    background-color: var(--bg-main) !important;
    padding-top: 1rem;
    padding-bottom: 1rem;
    color: var(--text-primary) !important;
    }

    /* Cards, tabelas e elementos principais */
    .main-pane .card,
    .modal-content,
    .dropdown-menu {
    background-color: var(--bg-card) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important; /* Sombra para profundidade */
    }
    .main-pane table,
    .modal table {
    background-color: var(--bg-card) !important;
    color: var(--text-primary) !important;
    }
    .main-pane th, .main-pane td,
    .modal th, .modal td {
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
    }

    /* Inputs, selects, textareas, popups */
    .main-pane input, .main-pane select, .main-pane textarea,
    .modal input, .modal select, .modal textarea,
    .dropdown-item {
    background-color: var(--bg-input) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
    }
    .main-pane input::placeholder,
    .modal input::placeholder {
    color: var(--text-secondary) !important;
    }

    /* Botões e hover */
    .main-pane button,
    .modal button {
    background-color: var(--bg-sidebar) !important;
    border-color: var(--bg-sidebar) !important;
    color: #fff !important;
    }
    .main-pane button:hover,
    .modal button:hover {
    filter: brightness(1.1);
    }

    /* Navbar e modais */
    .navbar { 
    background-color: var(--bg-card) !important; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important; 
    }
    .navbar-brand, .nav-link { color: var(--text-primary) !important; }

    /* Sobrescrever Bootstrap modais/popovers */
    .modal-backdrop { background-color: rgba(0,0,0,0.85) !important; }
    .popover { background-color: var(--bg-card) !important; color: var(--text-primary) !important; border: 1px solid var(--border) !important; }
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
                    <ul class="navbar-nav me-auto"></ul>

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
                                    {{ Auth::user()->nome }}
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

        <main class="py-4">
            <div class="container-fluid h-100">
                <div class="row main-content-row h-100">
                    @auth
                    <div class="col-md-2 bg-primary text-white p-0 h-100 rounded-lg">
                        <div class="list-group list-group-flush rounded-0">
                            <a href="{{ route('home') }}"
                               class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="bi bi-house-door-fill"></i> Início
                            </a>

                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.usuarios.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                                    <i class="bi bi-people-fill"></i> Usuários (todas OPMs)
                                </a>

                                <a href="{{ route('admin.viaturas.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('admin.viaturas.*') ? 'active' : '' }}">
                                    <i class="bi bi-car-front-fill"></i> Viaturas (todas)
                                </a>

                                <a href="{{ route('em_construcao') }}"
                                    class="list-group-item list-group-item-action bg-transparent text-white sidebar-link">
                                    <i class="bi bi-broadcast-pin"></i> Rádios (todas)
                                </a>

                                {{-- NOVO: OPMs (Admin) --}}
                                <a href="{{ route('em_construcao') }}"
                                    class="list-group-item list-group-item-action bg-transparent text-white sidebar-link">
                                    <i class="bi bi-diagram-3-fill"></i> OPMs
                                </a>

                                {{-- NOVO: Consultas --}}
                                <a href="{{ route('consultas.viaturas') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('consultas.*') ? 'active' : '' }}">
                                    <i class="bi bi-search"></i> Consultas
                                </a>

                                <a href="{{ route('em_construcao') }}"
                                    class="list-group-item list-group-item-action bg-transparent text-white sidebar-link">
                                    <i class="bi bi-clipboard-data-fill"></i> Relatórios (Admin)
                                </a>

                                <a href="{{ route('admin.manutencoes.index') }}"
                                    class="list-group-item list-group-item-action bg-transparent text-white sidebar-link">
                                    <i class="bi bi-tools"></i> Manutenções (Admin)
                                </a>

                            @elseif(Auth::user()->isP4())
                                <a href="{{ route('p4.viaturas.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('p4.viaturas.*') ? 'active' : '' }}">
                                    <i class="bi bi-car-front-fill"></i> Minhas Viaturas (P4)
                                </a>

                                <a href="{{ route('p4.manutencoes.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('p4.manutencoes.*') ? 'active' : '' }}">
                                    <i class="bi bi-tools"></i> Minhas Manutenções (P4)
                                </a>

                                <a href="{{ route('p4.radios.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('p4.radios.*') ? 'active' : '' }}">
                                    <i class="bi bi-broadcast-pin"></i> Meus Rádios (P4)
                                </a>

                                <a href="{{ route('p4.relatorios.index') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('p4.relatorios.*') ? 'active' : '' }}">
                                    <i class="bi bi-clipboard-data-fill"></i> Meus Relatórios (P4)
                                </a>

                                {{-- NOVO: Consultas (P4 também) --}}
                                <a href="{{ route('consultas.viaturas') }}"
                                   class="list-group-item list-group-item-action bg-transparent text-white sidebar-link {{ request()->routeIs('consultas.*') ? 'active' : '' }}">
                                    <i class="bi bi-search"></i> Consultas
                                </a>
                            @endif

                            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>

                            <a class="list-group-item list-group-item-action bg-danger text-white"
                               href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </div>
                    </div>

                    <div class="col-md-10 h-100 main-pane">
                        @yield('content')
                    </div>
                    @else
                        <div class="col-md-12 main-pane">
                            @yield('content')
                        </div>
                    @endauth
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- jQuery (necessário para Select2) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Scripts empilhados pelas views via @push('scripts') --}}
    @stack('scripts')

</body>
</html>
