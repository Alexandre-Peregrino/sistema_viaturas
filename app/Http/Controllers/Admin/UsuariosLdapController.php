<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Ldap\Authldap;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsuariosLdapController extends Controller
{
    /**
     * GET /admin/usuarios/ldap
     */
    public function index()
    {
        return view('admin.usuarios.ldap');
    }

    /**
     * GET /admin/usuarios/ldap/search?q= OU ?cpf=
     * Retorna JSON com resultados do AD.
     *
     * Observação: seu Authldap::searchByCpfService() retorna no máximo 1 usuário.
     */
    public function search(Request $request)
    {
        // Compatível com front antigo (?q=) e com front novo (?cpf=)
        $qRaw  = (string) $request->query('q', '');
        $cpfRaw = (string) $request->query('cpf', '');

        $q = trim($cpfRaw !== '' ? $cpfRaw : $qRaw);

        $limit = (int) $request->query('limit', 20);
        $limit = ($limit > 0 && $limit <= 50) ? $limit : 20;

        // Se não veio nada, devolve vazio
        if ($q === '') {
            return response()->json([]);
        }

        // Extrai apenas dígitos (CPF/matrícula numérica)
        $termDigits = preg_replace('/\D+/', '', $q);

        Log::info('[AD] ldap.search chamado', [
            'q' => $q,
            'digits' => $termDigits,
            'ip' => $request->ip(),
            'query_keys' => array_keys($request->query()),
        ]);

        /**
         * Regra:
         * - Se for CPF (11 dígitos), busca como CPF
         * - Se tiver >= 5 dígitos, tenta também (matrícula numérica etc.)
         * - Caso contrário, retorna vazio (evita varrer AD)
         */
        if (strlen($termDigits) < 5) {
            return response()->json([]);
        }

        $ldap = new Authldap();

        $results = [];
        $r = $ldap->searchByCpfService($termDigits);

        if ($r) {
            $results[] = $r;
        }

        // Marca quais já existem no banco local
        $out = [];
        foreach (array_slice($results, 0, $limit) as $r) {
            $cpf = $r['cpf'] ?? null;
            $mat = $r['matricula'] ?? null;

            $local = Usuario::query()
                ->when($cpf, fn($qb) => $qb->orWhere('cpf', $cpf))
                ->when($mat, fn($qb) => $qb->orWhere('matricula', $mat))
                ->first(['id', 'cpf', 'matricula']);

            $out[] = [
                'cpf' => $cpf,
                'matricula' => $mat,
                'nome' => $r['nome'] ?? null,
                'email' => $r['email'] ?? null,
                'login' => $r['login'] ?? null,
                'unidade' => $r['unidade'] ?? null,
                'exists_local' => (bool) $local,
                'local_usuario_id' => $local?->id,
            ];
        }

        Log::info('[AD] ldap.search resultado', [
            'count' => count($out),
            'cpf' => $termDigits,
        ]);

        return response()->json($out);
    }

    /**
     * POST /admin/usuarios/ldap/import
     * Cria/atualiza usuário local mínimo para permitir setar permissões.
     */
    public function import(Request $request)
    {
        $request->validate([
            'cpf' => ['nullable', 'string', 'max:32'],
            'matricula' => ['nullable', 'string', 'max:64'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', ''));
        $matricula = trim((string) $request->input('matricula', ''));

        // Prioriza CPF; se não tiver, tenta “matrícula” como dígitos
        $term = $cpf !== '' ? $cpf : preg_replace('/\D+/', '', $matricula);

        if ($term === '' || strlen($term) < 5) {
            return back()->with('error', 'Informe CPF (11 dígitos) ou matrícula numérica.');
        }

        Log::info('[AD] ldap.import chamado', [
            'cpf' => $cpf,
            'matricula' => $matricula,
            'term' => $term,
            'ip' => $request->ip(),
        ]);

        $ldap = new Authldap();
        $r = $ldap->searchByCpfService($term);

        if (!$r) {
            return back()->with('error', 'Usuário não encontrado no AD.');
        }

        $cpfFound = $r['cpf'] ?? null;
        $matFound = $r['matricula'] ?? null;

        /** @var Usuario|null $u */
        $u = Usuario::query()
            ->when($cpfFound, fn($qb) => $qb->orWhere('cpf', $cpfFound))
            ->when($matFound, fn($qb) => $qb->orWhere('matricula', $matFound))
            ->first();

        if (!$u) {
            $u = new Usuario();
        }

        // Helper seguro: só seta se a coluna estiver em $fillable (ou se o model não define fillable, seta direto)
        $fillable = method_exists($u, 'getFillable') ? $u->getFillable() : [];
        $canSet = function (string $field) use ($fillable): bool {
            return empty($fillable) || in_array($field, $fillable, true);
        };

        if ($cpfFound && $canSet('cpf')) {
            $u->cpf = $cpfFound;
        }

        if ($matFound && $canSet('matricula')) {
            $u->matricula = $matFound;
        }

        if (!empty($r['nome']) && $canSet('nome')) {
            $u->nome = $r['nome'];
        }

        if (!empty($r['email']) && $canSet('email')) {
            $u->email = $r['email'];
        }

        // Se você tem coluna "login" no seu Usuario, ele seta.
        if (!empty($r['login']) && $canSet('login')) {
            $u->login = $r['login'];
        }

        // Se existir 'ativo' e estiver null, ativa
        if ($canSet('ativo') && $u->getAttribute('ativo') === null) {
            $u->ativo = true;
        }

        $u->save();

        return redirect()
            ->route('admin.usuarios.edit', $u->id)
            ->with('success', 'Usuário importado. Agora defina as permissões.');
    }
}
