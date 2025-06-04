<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Frota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Menu lateral -->
        <div class="bg-primary text-white p-3" style="min-width: 200px; height: 100vh;">
            <h4>Sistema de Frota</h4>
            <hr class="text-white">

            @auth
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('home') }}">Início</a>
                    </li>

                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('admin.usuarios.index') }}">Usuários (todas OPMs)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('admin.viaturas.index') }}">Viaturas (todas)</a>
                        </li>
                    @elseif(auth()->user()->isP4())
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">Usuários da minha OPM</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('p4.viaturas.index') }}">Minhas Viaturas</a>
                        </li>
                    @endif

                    <li class="nav-item mt-3">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-light btn-sm w-100" type="submit">Sair</button>
                        </form>
                    </li>
                </ul>
            @endauth
        </div>

        <!-- Conteúdo principal -->
        <div class="p-4 flex-grow-1">
            @yield('content')
        </div>
    </div>

    <!-- Adicionar script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>