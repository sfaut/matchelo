<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User\Role;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

/**
 * Entrée du mode « Gérer le club » : tableau de bord du premier club animé (par nom), ou
 * liste des clubs si l'utilisateur n'en anime aucun.
 */
class ManagementHomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        $club = $user->clubs()
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->orderBy('name')
            ->first();

        return $club === null
            ? redirect()->route('clubs.index')
            : redirect()->route('clubs.dashboard', $club);
    }
}
