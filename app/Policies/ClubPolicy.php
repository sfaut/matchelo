<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;

/**
 * Droits sur les clubs.
 */
class ClubPolicy
{
    /**
     * Gérer un club (tableau de bord, élèves) : l'un de ses animateurs, ou un administrateur.
     */
    public function manage(User $user, Club $club): bool
    {
        if ($user->is_administrator) {
            return true;
        }

        return $club->members()
            ->whereKey($user->id)
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->exists();
    }

    /**
     * Modifier un club : mêmes droits que pour le gérer.
     */
    public function update(User $user, Club $club): bool
    {
        return $this->manage($user, $club);
    }
}
