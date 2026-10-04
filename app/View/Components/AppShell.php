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
 * un animateur, bascule clair / sombre, initiales) et barre latérale desktop. En mode
 * « jouer » : mes clubs, création de club, déconnexion. En mode « gérer » un club : sélecteur
 * des clubs animés, navigation de gestion et code d'invitation.
 */
class AppShell extends Component
{
    /**
     * Sections de la navigation de gestion, avec la route de chacune.
     *
     * @var array<string, string>
     */
    public const array MANAGE_SECTIONS = [
        'dashboard' => 'clubs.dashboard',
        'players' => 'clubs.players.create',
        'settings' => 'clubs.edit',
    ];

    /**
     * Clubs de l'utilisateur, par nom.
     *
     * @var Collection<int, Club>
     */
    public Collection $clubs;

    /**
     * Clubs que l'utilisateur anime (rôle ANIMATOR), par nom.
     *
     * @var Collection<int, Club>
     */
    public Collection $animatedClubs;

    /**
     * @param  User  $user  utilisateur connecté
     * @param  string  $mode  mode courant du switch : manage (gérer le club) ou play (jouer)
     * @param  Club|null  $club  club géré, en mode manage
     * @param  string  $section  section de gestion courante (clef de MANAGE_SECTIONS), en mode manage
     */
    public function __construct(
        #[CurrentUser]
        public User $user,
        public string $mode = 'play',
        public ?Club $club = null,
        public string $section = 'dashboard',
    ) {
        $this->clubs = $user->clubs()->orderBy('name')->get();
        $this->animatedClubs = $user->clubs()
            ->wherePivot('role_id', Role::idFor(Role::ANIMATOR))
            ->orderBy('name')
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
     * Indique si la coque affiche la gestion d'un club.
     */
    public function isManaging(): bool
    {
        return $this->mode === 'manage' && $this->club !== null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.app-shell');
    }
}
