<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Manutencao;

class ManutencaoPolicy
{
    /**
     * Admin pode tudo em qualquer ação desta Policy.
     * Evita 403 acidentais quando faltar algum método.
     */
    public function before(Usuario $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
        // retornar null deixa seguir para os outros métodos
    }

    /**
     * Listagem: P4 pode listar (controller filtra por OPM).
     */
    public function viewAny(Usuario $user): bool
    {
        return $user->isP4();
    }

    /**
     * Ver detalhe: P4 só se a manutenção for de veículo da sua OPM.
     */
    public function view(Usuario $user, Manutencao $manutencao): bool
    {
        $opmId = (int) optional($manutencao->veiculo)->opm_id; // veiculo pode ser null
        return $user->isP4() && $opmId === (int) $user->opm_id;
    }

    /**
     * Editar/Atualizar: mesma regra do view.
     */
    public function update(Usuario $user, Manutencao $manutencao): bool
    {
        $opmId = (int) optional($manutencao->veiculo)->opm_id;
        return $user->isP4() && $opmId === (int) $user->opm_id;
    }

    /**
     * Excluir: somente Admin (já coberto pelo before, mas deixo explícito).
     */
    public function delete(Usuario $user, Manutencao $manutencao): bool
    {
        return false; // Admin já foi liberado no before(); P4 não pode
    }
}
