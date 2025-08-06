@extends('layouts.app') {{-- ou outro layout que você estiver usando --}}

@section('title', 'Resultado dos Rádios')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Resultado dos Rádios</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark text-center">
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Status</th>
                    <th>OPM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($radios as $radio)
                    <tr>
                        <td>{{ $radio->marca }}</td>
                        <td>{{ $radio->modelo }}</td>
                        <td>{{ ucfirst($radio->status) }}</td>
                        <td>{{ $radio->opm->sigla ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Nenhum rádio encontrado com os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.relatorios.radios.filtros') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
