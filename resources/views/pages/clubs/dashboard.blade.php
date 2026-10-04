<?php

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use App\Support\AttendanceReport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Tableau de bord d'un club, pour ses animateurs et les administrateurs (ClubPolicy::manage) :
 * séance du jour, assiduité, membres à relancer, classement. Le tournoi en cours et « À traiter »
 * s'affichent vides tant que ces données n'existent pas.
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
     * Nombre de séances de l'histogramme d'assiduité.
     *
     * @var int
     */
    public const int ATTENDANCE_SESSIONS = 5;

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
     * Rapport d'assiduité du club.
     */
    #[Computed]
    public function report(): AttendanceReport
    {
        return AttendanceReport::for($this->club);
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
     * Séance du jour, ou null tant que personne n'y est pointé.
     */
    #[Computed]
    public function todaySession(): ?ClubSession
    {
        return $this->report->todaySession();
    }

    /**
     * Barres de l'histogramme : taux de présence des dernières séances, de la plus ancienne à
     * la plus récente.
     *
     * @return list<array{label: string, rate: int, is_today: bool}>
     */
    #[Computed]
    public function attendanceBars(): array
    {
        return $this->report->recentSessions(self::ATTENDANCE_SESSIONS)
            ->map(fn (ClubSession $session): array => [
                'label' => $session->held_on->isToday()
                    ? __('clubs.dashboard.today_short')
                    : $session->held_on->locale(app()->getLocale())->isoFormat(__('clubs.dashboard.bar_date_format')),
                'rate' => $this->report->rate($session, $this->membersCount),
                'is_today' => $session->held_on->isToday(),
            ])
            ->all();
    }

    /**
     * Taux de présence moyen des séances de l'histogramme, ou null sans séance.
     */
    #[Computed]
    public function averageRate(): ?int
    {
        return $this->report->averageRate($this->report->recentSessions(self::ATTENDANCE_SESSIONS), $this->membersCount);
    }

    /**
     * Membres absents plusieurs fois de suite aux dernières séances.
     *
     * @return BaseCollection<int, array{user: User, absences: non-negative-int}>
     */
    #[Computed]
    public function toFollowUp(): BaseCollection
    {
        return $this->report->consecutiveAbsences();
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
                <h1 class="text-h3 desk:text-h3-lg">{{ __('clubs.dashboard.greeting', ['name' => Auth::user()->fullName()]) }}</h1>
            </div>
            <a href="{{ route('clubs.players.create', $club) }}" class="mx-btn">{{ __('clubs.dashboard.add_player') }}</a>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.25fr_1fr_.9fr]">
            @php($present_today = $this->todaySession?->attendees_count ?? 0)
            <section class="mx-card flex flex-col gap-3.5 p-6">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="mx-label">{{ __('clubs.dashboard.today_session') }}</h2>
                    @if ($this->todaySession)
                        <span class="mx-badge">{{ __('clubs.dashboard.in_progress') }}</span>
                    @endif
                </div>
                <div class="flex items-baseline gap-2.5">
                    <span class="text-figure-lg">{{ $present_today }}</span>
                    <span class="text-3xl font-bold">{{ trans_choice('clubs.attendance.present_out_of', $this->membersCount, ['total' => $this->membersCount]) }}</span>
                </div>
                <x-presence-squares :present="$present_today" :total="$this->membersCount" />
                <span class="text-md text-muted">
                    {{ trans_choice('clubs.dashboard.not_checked', $this->membersCount - $present_today, ['count' => $this->membersCount - $present_today]) }}
                </span>
                <div>
                    <a href="{{ route('clubs.attendance', $club) }}" class="mx-btn mx-btn--primary">
                        {{ $this->todaySession ? __('clubs.dashboard.edit_attendance') : __('clubs.dashboard.take_attendance') }}
                    </a>
                </div>
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
                <div class="flex items-baseline justify-between gap-2">
                    <h2 class="mx-label">{{ __('clubs.dashboard.attendance') }}</h2>
                    @if ($this->averageRate !== null)
                        <b class="text-base">{{ __('clubs.dashboard.average', ['rate' => Number::percentage($this->averageRate)]) }}</b>
                    @endif
                </div>
                @if ($this->attendanceBars === [])
                    <p class="text-md text-muted">{{ __('clubs.dashboard.no_sessions_yet') }}</p>
                @else
                    <x-attendance-bars :bars="$this->attendanceBars" />
                @endif
            </section>

            <section class="flex flex-col gap-3.5 mx-border bg-surface p-6">
                <h2 class="mx-label">{{ __('clubs.dashboard.to_follow_up') }}</h2>
                @forelse ($this->toFollowUp as $row)
                    <div class="flex items-center gap-3" wire:key="follow-up-{{ $row['user']->id }}">
                        <span class="grid size-9 flex-none place-items-center mx-border bg-surface-2 text-sm font-bold">{{ $row['user']->initials() }}</span>
                        <div class="flex min-w-0 flex-col">
                            <b class="truncate">{{ $row['user']->fullName() }}</b>
                            <span class="text-sm text-muted">{{ trans_choice('clubs.dashboard.absent_in_a_row', $row['absences'], ['count' => $row['absences']]) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-md text-muted">
                        {{ $this->attendanceBars === [] ? __('clubs.dashboard.no_sessions_yet') : __('clubs.dashboard.nobody_to_follow_up') }}
                    </p>
                @endforelse
                <a href="{{ route('clubs.attendance', $club) }}" class="text-md font-bold underline">{{ __('clubs.dashboard.all_attendance') }}</a>
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
