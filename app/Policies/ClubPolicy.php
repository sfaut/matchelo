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
     * Un club se modifie par l'un de ses animateurs, ou par un administrateur.
     */
    public function update(User $user, Club $club): bool
    {
        if ($user->is_administrator) {
            return true;
        }

        return $club->members()
            ->whereKey($user->id)
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->exists();
    }
}
