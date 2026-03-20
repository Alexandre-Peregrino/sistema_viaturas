<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Manutencao;

class ManutencaoPolicy
{
    /**
     * Admin pode tudo em qualquer ação desta Policy.
     */
    public function before(Usuario $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null; // segue para os outros métodos
    }

    /**
     * Listagem: P4 pode listar (controller filtra por OPM).
     */
    public function viewAny(Usuario $user): bool
    {
        return $user->isP4() || $user->isAdmin();
    }

    /**
     * Ver detalhe: P4 só se for de veículo da sua OPM.
     */
    public function view(Usuario $user, Manutencao $manutencao): bool
    {
        $opmId = (int) optional($manutencao->veiculo)->opm_id; // veiculo pode ser null
        return $user->isP4() && $opmId === (int) $user->opm_id;
    }

    /**
     * Criar: P4 pode criar para sua OPM (normalmente o controller já amarra o veiculo_id).
     * Se você não quiser que P4 crie, retorne false.
     */
    public function create(Usuario $user): bool
    {
        return $user->isP4();
    }

    /**
     * Atualizar: mesma regra do view.
     */
    public function update(Usuario $user, Manutencao $manutencao): bool
    {
        $opmId = (int) optional($manutencao->veiculo)->opm_id;
        return $user->isP4() && $opmId === (int) $user->opm_id;
    }

    /**
     * Excluir: somente Admin (P4 não pode).
     * Admin já foi liberado no before().
     */
    public function delete(Usuario $user, Manutencao $manutencao): bool
    {
        return false;
    }

    /**
     * Opcional: se usar SoftDeletes no futuro.
     */
    public function restore(Usuario $user, Manutencao $manutencao): bool
    {
        return false;
    }

    public function forceDelete(Usuario $user, Manutencao $manutencao): bool
    {
        return false;
    }
}
