<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Veiculo;

class ViaturaPolicy
{
    /**
     * Admin e P4 podem listar; o filtro por OPM é feito no Controller (index).
     */
    public function viewAny(Usuario $user): bool
    {
        return $user->isAdmin() || $user->isP4();
    }

    /**
     * Admin vê tudo; P4 só vê se a viatura for da sua OPM.
     */
    public function view(Usuario $user, Veiculo $veiculo): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isP4())   return (int)$veiculo->opm_id === (int)$user->opm_id;
        return false;
    }

    /**
     * Admin edita tudo; P4 só edita se for da sua OPM.
     * Ajuste as regras conforme sua necessidade.
     */
    public function update(Usuario $user, Veiculo $veiculo): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isP4())   return (int)$veiculo->opm_id === (int)$user->opm_id;
        return false;
    }

    /**
     * Por padrão, só Admin pode excluir.
     */
    public function delete(Usuario $user, Veiculo $veiculo): bool
    {
        return $user->isAdmin();
    }
}
