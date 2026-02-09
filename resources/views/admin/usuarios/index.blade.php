@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Lista de Usuários</h1>

    {{-- Campo de busca (LOCAL) --}}
    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mb-3 d-flex">
        <input type="text" name="busca" class="form-control me-2"
               placeholder="Buscar por nome, CPF ou matrícula"
               value="{{ request('busca') }}">
        <button type="submit" class="btn btn-primary">Buscar</button>
        @if(request('busca'))
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary ms-2">Limpar</a>
        @endif
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- TABELA LOCAL --}}
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Perfil</th>
                <th>Permitido</th>
                <th>OPM</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $usuario->nome }}</td>
                    <td>{{ strtoupper($usuario->perfil) }}</td>
                    <td>{{ $usuario->permitido ? 'Sim' : 'Não' }}</td>
                    <td>{{ $usuario->opm->sigla ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">
                            Editar
                        </a>

                        {{-- DELETE COM CONFIRMAÇÃO SEGURA --}}
                        <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}"
                              method="POST"
                              style="display:inline-block;"
                              onsubmit="return confirm('Deseja realmente excluir o usuário {{ addslashes($usuario->nome) }} (CPF: {{ $usuario->cpf }})? Esta ação não poderá ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                Excluir
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhum usuário encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
async function ldapSearch() {
    const qInput = document.getElementById('ldap_q');
    const q = (qInput?.value || '').trim();
    const status = document.getElementById('ldap_status');
    const rows = document.getElementById('ldap_rows');

    if (q.length < 3) {
        if (status) status.textContent = 'Digite ao menos 3 caracteres.';
        return;
    }

    if (status) status.textContent = 'Buscando no AD/LDAP...';
    if (rows) rows.innerHTML = `<tr><td colspan="4" class="text-muted">Carregando...</td></tr>`;

    try {
        const url = `{{ route('admin.usuarios.ldap.search') }}?q=${encodeURIComponent(q)}&limit=20`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

        if (!res.ok) throw new Error('HTTP ' + res.status);

        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) {
            if (status) status.textContent = 'Nenhum resultado no AD.';
            if (rows) rows.innerHTML = `<tr><td colspan="4" class="text-muted">Nenhum usuário encontrado.</td></tr>`;
            return;
        }

        if (status) status.textContent = `${data.length} resultado(s).`;

        if (rows) {
            rows.innerHTML = data.map(r => {
                const cpf = r.cpf ?? '';
                const mat = r.matricula ?? '';
                const info = [r.nome, r.email].filter(Boolean).join(' • ');
                const exists = !!r.exists_local;

                let action = '';
                if (exists && r.local_usuario_id) {
                    action = `<a class="btn btn-sm btn-outline-primary" href="/admin/usuarios/${r.local_usuario_id}/edit">Editar permissões</a>`;
                } else {
                    action = `<button class="btn btn-sm btn-primary" type="button" onclick="ldapImport('${escapeHtml(cpf)}','${escapeHtml(mat)}')">Importar</button>`;
                }

                return `
                    <tr>
                        <td class="fw-semibold">${escapeHtml(cpf)}</td>
                        <td>${escapeHtml(mat)}</td>
                        <td class="text-muted small">${escapeHtml(info)}</td>
                        <td class="text-end">${action}</td>
                    </tr>
                `;
            }).join('');
        }

    } catch (e) {
        if (status) status.textContent = 'Erro ao consultar o AD.';
        if (rows) rows.innerHTML = `<tr><td colspan="4" class="text-danger">Falha na busca.</td></tr>`;
    }
}

function ldapImport(cpf, matricula) {
    document.getElementById('ldap_import_cpf').value = cpf || '';
    document.getElementById('ldap_import_matricula').value = matricula || '';
    document.getElementById('ldap_import_form').submit();
}

function ldapClear() {
    const q = document.getElementById('ldap_q');
    const status = document.getElementById('ldap_status');
    const rows = document.getElementById('ldap_rows');

    if (q) q.value = '';
    if (status) status.textContent = '';
    if (rows) {
        rows.innerHTML = `<tr><td colspan="4" class="text-muted">Faça uma busca para encontrar um usuário no AD e importá-lo.</td></tr>`;
    }
}

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#039;'
    }[m]));
}
</script>
@endpush
