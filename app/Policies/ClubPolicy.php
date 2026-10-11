<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

/**
 * Droits sur les clubs.
 *
 * @see docs/roles.md
 */
class ClubPolicy
{
    /**
     * Gérer un club (tableau de bord, élèves, présences) : l'un de ses présidents ou
     * animateurs, ou un administrateur.
     */
    public function manage(User $user, Club $club): bool
    {
        if ($user->is_administrator) {
            return true;
        }

        return $user->managedClubs()->whereKey($club->id)->exists();
    }

    /**
     * Modifier un club : l'un de ses présidents, ou un administrateur.
     */
    public function update(User $user, Club $club): bool
    {
        return $user->is_administrator || $user->presides($club);
    }

    /**
     * Planifier les événements d'un club en masse (EventPlanner : génération, modification,
     * suppression, composition) : l'un de ses présidents, ou un administrateur.
     */
    public function planEvents(User $user, Club $club): bool
    {
        return $user->is_administrator || $user->presides($club);
    }
}
