<?php

namespace App\View\Components;

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use Closure;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

/**
 * Coque de l'application pour un utilisateur connecté : en-tête (logo, switch de mode pour
 * un animateur, bascule clair / sombre, initiales) et barre latérale desktop (mes clubs,
 * création de club, déconnexion).
 */
class AppShell extends Component
{
    /**
     * Clubs de l'utilisateur, par nom.
     *
     * @var Collection<int, Club>
     */
    public Collection $clubs;

    /**
     * Clubs que l'utilisateur anime (rôle ANIMATOR).
     *
     * @var Collection<int, Club>
     */
    public Collection $animatedClubs;

    /**
     * @param  User  $user  utilisateur connecté
     * @param  string  $mode  mode courant du switch : manage (gérer le club) ou play (jouer)
     */
    public function __construct(
        #[CurrentUser]
        public User $user,
        public string $mode = 'play',
    ) {
        $this->clubs = $user->clubs()->orderBy('name')->get();
        $this->animatedClubs = $user->clubs()
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->get();
    }

    /**
     * Indique si l'utilisateur anime l'un de ses clubs, et voit donc le switch de mode.
     */
    public function isAnimator(): bool
    {
        return $this->animatedClubs->isNotEmpty();
    }

    /**
     * Indique si l'utilisateur anime un club donné de sa liste.
     *
     * @param  Club  $club  club de la liste
     */
    public function animates(Club $club): bool
    {
        return $this->animatedClubs->contains($club);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.app-shell');
    }
}
