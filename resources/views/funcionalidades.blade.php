@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center bg-info text-white">
                    <h2 class="mb-0">Funcionalidades do Sistema de Frota</h2>
                </div>

                <div class="card-body">
                    <h4 class="mb-3">Visão Geral:</h4>
                    <p>Nosso sistema oferece um controle abrangente para a gestão da sua frota de veículos e equipamentos, dividindo o acesso e funcionalidades por perfil de usuário.</p>

                    <h4 class="mt-4 mb-3">Funcionalidades para Administradores:</h4>
                    <ul>
                        <li><strong>Gestão Completa de Viaturas:</strong> Cadastrar, editar, visualizar e excluir viaturas.</li>
                        <li><strong>Gestão de Usuários:</strong> Criar, editar e gerenciar perfis de acesso (Admin, P4, etc.).</li>
                        <li><strong>Gestão de Manutenções:</strong> Registrar e acompanhar o histórico de manutenções dos veículos.</li>
                        <li><strong>Gestão de Rádios:</strong> Administrar o inventário de rádios.</li>
                        <li><strong>Gestão de Marcas/Modelos:</strong> Manter um catálogo de marcas e modelos de veículos.</li>
                        <li><strong>Relatórios Gerais:</strong> Acesso a relatórios detalhados e abrangentes de toda a frota.</li>
                    </ul>

                    <h4 class="mt-4 mb-3">Funcionalidades para Usuários P4:</h4>
                    <ul>
                        <li><strong>Visualização e Edição Restrita de Viaturas:</strong> Acesso e edição de dados de viaturas pertencentes à sua OPM específica (com campos de edição limitados).</li>
                        <li><strong>Acompanhamento de Manutenções:</strong> Visualizar manutenções relacionadas à sua OPM.</li>
                        <li><strong>Visualização de Rádios:</strong> Consultar rádios associados à sua OPM.</li>
                        <li><strong>Relatórios por OPM:</strong> Acesso a relatórios focados na frota da sua unidade.</li>
                    </ul>

                    <p class="text-center mt-4">
                        <a href="{{ route('home') }}" class="btn btn-secondary">Voltar para a Página Inicial</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection