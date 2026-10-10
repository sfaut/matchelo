<?php

namespace App\View\Components;

use App\Models\Club;
use App\Models\User;
use Closure;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

/**
 * Coque de l'application pour un utilisateur connecté : en-tête (logo, switch de mode pour
 * un président ou animateur, bascule clair / sombre, initiales) et barre latérale desktop. En mode
 * « jouer » : mes clubs, création de club, déconnexion. En mode « gérer » un club : sélecteur
 * des clubs gérés, navigation de gestion et code d'invitation.
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
        'attendance' => 'clubs.attendance',
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
     * Clubs que l'utilisateur gère (président ou animateur), par nom.
     *
     * @var Collection<int, Club>
     */
    public Collection $managedClubs;

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
        $this->managedClubs = $user->managedClubs()
            ->orderBy('name')
            ->get();
    }

    /**
     * Indique si l'utilisateur gère l'un de ses clubs, et voit donc le switch de mode.
     */
    public function isManager(): bool
    {
        return $this->managedClubs->isNotEmpty();
    }

    /**
     * Indique si l'utilisateur gère un club donné de sa liste.
     *
     * @param  Club  $club  club de la liste
     */
    public function manages(Club $club): bool
    {
        return $this->managedClubs->contains($club);
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
