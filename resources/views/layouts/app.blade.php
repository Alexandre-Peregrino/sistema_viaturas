<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Frota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .form-check-input {
            appearance: auto !important;
            -webkit-appearance: checkbox !important;
            -moz-appearance: checkbox !important;
            border: 1px solid #6c757d !important;
            background-color: #fff !important;
        }
    </style>

    <style>
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
            --bg-card: #1f1f1f;
            --bg-input: #2d2d2d;
            --bg-sidebar: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            --text-primary: #2e2b2b;
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
        .main-pane .card,
        .modal-content,
        .dropdown-menu {
            background-color: var(--bg-card) !important;
            border-color: var(--border) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
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
        .navbar {
            background-color: var(--bg-card) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
        }
        .navbar-brand, .nav-link { color: var(--text-primary) !important; }
        .modal-backdrop { background-color: rgba(0,0,0,0.85) !important; }
        .popover { background-color: var(--bg-card) !important; color: var(--text-primary) !important; border: 1px solid var(--border) !important; }
    </style>

</head>
<body>
    <div class="d-flex">
        <div class="bg-primary text-white p-3" style="min-width: 200px; height: 100vh;">
            <h4>Sistema de Frota</h4>
            <hr class="text-white">
            @auth
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('home') }}">Início</a></li>
                @if(auth()->user()->isAdmin())
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('admin.usuarios.index') }}">Usuários (todas OPMs)</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('admin.viaturas.index') }}">Viaturas (todas)</a></li>
                @elseif(auth()->user()->isP4())
                    <li class="nav-item"><a class="nav-link text-white" href="#">Usuários da minha OPM</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('p4.viaturas.index') }}">Minhas Viaturas</a></li>
                @endif
                <li class="nav-item mt-3">
                    <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-outline-light btn-sm w-100" type="submit">Sair</button></form>
                </li>
            </ul>
            @endauth
        </div>

        <div class="p-4 flex-grow-1">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
