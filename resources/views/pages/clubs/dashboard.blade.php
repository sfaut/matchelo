<?php

use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Tableau de bord d'un club, pour ses animateurs et les administrateurs (ClubPolicy::manage).
 * Séances, tournois et assiduité s'affichent vides tant que ces données n'existent pas.
 */
new class extends Component
{
    /**
     * Nombre de joueurs du classement affiché.
     *
     * @var int
     */
    public const int RANKING_SIZE = 5;

    /**
     * Club géré.
     */
    #[Locked]
    public Club $club;

    /**
     * Vérifie le droit de gérer le club.
     *
     * @throws AuthorizationException si l'utilisateur n'anime pas le club et n'est pas administrateur
     */
    public function mount(Club $club): void
    {
        $this->authorize('manage', $club);

        $this->club = $club;
    }

    /**
     * Date du jour, dans la langue courante (« Jeudi 1er octobre »).
     */
    #[Computed]
    public function today(): string
    {
        return Str::ucfirst(now()->locale(app()->getLocale())->isoFormat(__('clubs.dashboard.date_format')));
    }

    /**
     * Nombre de membres du club.
     */
    #[Computed]
    public function membersCount(): int
    {
        return $this->club->members()->count();
    }

    /**
     * Meilleurs ELO du club, du premier au dernier.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function ranking(): Collection
    {
        return $this->club->members()
            ->orderByDesc('elo')
            ->orderBy('last_name')
            ->limit(self::RANKING_SIZE)
            ->get();
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.dashboard.title', ['club' => $this->club->name]));
    }
};
?>

<x-app-shell mode="manage" :club="$club" section="dashboard">
    <div class="flex flex-col gap-5.5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <span class="mx-label">{{ $this->today }}</span>
                <h1 class="text-h3 desk:text-h3-lg">{{ __('clubs.dashboard.greeting', ['name' => Auth::user()->addressName()]) }}</h1>
            </div>
            <a href="{{ route('clubs.players.create', $club) }}" class="mx-btn">{{ __('clubs.dashboard.add_player') }}</a>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.25fr_1fr_.9fr]">
            <section class="mx-card flex flex-col gap-3.5 p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.today_session') }}</h2>
                <p class="text-md text-muted">{{ __('clubs.dashboard.no_session') }}</p>
            </section>

            <section class="flex flex-col gap-3.5 mx-border bg-surface p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.live_tournament') }}</h2>
                <p class="text-md text-muted">{{ __('clubs.dashboard.no_tournament') }}</p>
            </section>

            <section class="flex flex-col gap-3 mx-border bg-surface p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.to_handle') }}</h2>
                <p class="text-md text-muted">{{ __('clubs.dashboard.nothing_to_handle') }}</p>
            </section>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.25fr_1fr_.9fr]">
            <section class="flex flex-col gap-3 mx-border bg-surface p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.attendance') }}</h2>
                <p class="text-md text-muted">{{ __('clubs.dashboard.no_sessions_yet') }}</p>
            </section>

            <section class="flex flex-col gap-3.5 mx-border bg-surface p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.to_follow_up') }}</h2>
                <p class="text-md text-muted">{{ __('clubs.dashboard.no_sessions_yet') }}</p>
            </section>

            <section class="flex flex-col gap-3 mx-border bg-surface p-6">
                <div class="flex items-baseline justify-between gap-2">
                    <h2 class="mx-label">{{ __('clubs.dashboard.ranking') }}</h2>
                    <span class="text-sm text-muted">{{ trans_choice('clubs.index.members', $this->membersCount) }}</span>
                </div>
                @foreach ($this->ranking as $rank => $player)
                    <div class="flex items-center gap-3 text-base" wire:key="ranking-{{ $player->id }}">
                        <span @class([
                            'grid size-7 flex-none place-items-center mx-border text-sm font-bold',
                            'bg-accent text-on-accent' => $rank === 0,
                            'bg-surface' => $rank > 0,
                        ])>{{ $rank + 1 }}</span>
                        <b class="min-w-0 flex-1 truncate">{{ $player->fullName() }}</b>
                        <span class="font-bold tabular-nums">{{ Number::format($player->elo) }}</span>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</x-app-shell>
