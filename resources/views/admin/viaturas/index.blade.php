@extends('layouts.app') {{-- GARANTIR QUE ESTENDE O LAYOUT CORRETO --}}

@section('content')
<div class="container mt-4" style="background-color: #f0f0f0; padding: 20px; border-radius: 8px;"> {{-- Card com fundo cinza claro para o conteúdo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Viaturas - Administração</h1>
        <a href="{{ route('admin.viaturas.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nova Viatura
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($viaturas->isEmpty())
        <div class="alert alert-info text-center">
            Nenhuma viatura cadastrada.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Prefixo</th>
                        <th>Placa</th>
                        <th>Modelo</th> {{-- Cabeçalho continua "Modelo" --}}
                        <th>Tipo</th> {{-- COLUNA TIPO --}}
                        <th>OPM</th> {{-- COLUNA OPM --}}
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($viaturas as $viatura)
                    <tr>
                        <td>{{ $viatura->id }}</td>
                        <td>{{ $viatura->prefixo }}</td>
                        <td>{{ $viatura->placa }}</td>
                        <td>{{ $viatura->marca_modelo ?? '-' }}</td> {{-- EXIBE 'marca_modelo' --}}
                        <td>{{ $viatura->tipo_veiculo ?? 'N/A' }}</td> {{-- EXIBE A STRING DIRETA DO TIPO DE VEÍCULO --}}
                        <td>{{ $viatura->opm->sigla ?? 'N/A' }}</td> {{-- EXIBE A SIGLA DA OPM --}}
                        <td>
                            <a href="{{ route('admin.viaturas.edit', $viatura->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('admin.viaturas.destroy', $viatura->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmar exclusão da viatura {{ $viatura->prefixo }}?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
