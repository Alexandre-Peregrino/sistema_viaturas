@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Relatórios</h2>

    <div class="row g-4">
        <div class="col-md-3">
            <a href="{{ route('admin.relatorios.viaturas.filtros') }}" class="btn btn-outline-primary w-100 p-4">
                <i class="bi bi-car-front-fill fs-4"></i><br>Veículos
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.relatorios.usuarios.filtros') }}" class="btn btn-outline-primary w-100 p-4">
                <i class="bi bi-person-badge-fill fs-4"></i><br>Usuários
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.relatorios.radios.filtros') }}" class="btn btn-outline-primary w-100 p-4">
                <i class="bi bi-broadcast-pin fs-4"></i><br>Rádios
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.relatorios.manutencoes.filtros') }}" class="btn btn-outline-primary w-100 p-4">
                <i class="bi bi-tools fs-4"></i><br>Manutenções
            </a>
        </div>
    </div>
</div>
@endsection
