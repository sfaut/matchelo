<?php

use App\Models\AttendanceStatus;
use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use App\Support\AttendanceReport;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Appel d'une séance (ClubPolicy::manage) : aujourd'hui ou l'une des séances précédentes, un
 * bouton Présent / Absent par membre ; la séance est créée au premier pointage.
 */
new class extends Component
{
    /**
     * Nombre de séances précédentes proposées en plus d'aujourd'hui.
     *
     * @var int
     */
    public const int PREVIOUS_SESSIONS = 4;

    /**
     * Club géré.
     */
    #[Locked]
    public Club $club;

    /**
     * Date de la séance pointée (AAAA-MM-JJ), aujourd'hui par défaut.
     */
    #[Url]
    public string $date = '';

    /**
     * Filtre des membres par nom.
     */
    public string $search = '';

    /**
     * Vérifie le droit de gérer le club et ramène une date non proposée à aujourd'hui.
     *
     * @throws AuthorizationException si l'utilisateur n'anime pas le club et n'est pas administrateur
     */
    public function mount(Club $club): void
    {
        $this->authorize('manage', $club);

        $this->club = $club;
        $this->date = in_array($this->date, $this->availableDates(), true) ? $this->date : $this->today();
    }

    /**
     * Date du jour (AAAA-MM-JJ).
     */
    public function today(): string
    {
        return CarbonImmutable::today()->toDateString();
    }

    /**
     * Dates proposées : aujourd'hui, puis les séances précédentes, de la plus récente à la plus ancienne.
     *
     * @return list<string>
     */
    #[Computed]
    public function availableDates(): array
    {
        $previous_dates = $this->club->events()
            ->whereDate('scheduled_at', '<', $this->today())
            ->orderByDesc('scheduled_at')
            ->limit(self::PREVIOUS_SESSIONS)
            ->pluck('scheduled_at')
            ->map(fn (CarbonImmutable $scheduled_at): string => $scheduled_at->toDateString())
            ->all();

        return [$this->today(), ...$previous_dates];
    }

    /**
     * Séance de la date choisie, ou null tant que personne n'y est pointé.
     */
    #[Computed]
    public function session(): ?Event
    {
        return $this->club->events()->whereDate('scheduled_at', $this->date)->first();
    }

    /**
     * Identifiants des membres présents à la séance choisie.
     *
     * @return list<int>
     */
    #[Computed]
    public function presentIds(): array
    {
        return $this->session?->attendees()->pluck('users.id')->map(fn (mixed $id): int => (int) $id)->all() ?? [];
    }

    /**
     * Membres du club, par nom, filtrés par la recherche.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        $search = Str::lower(trim($this->search));

        return $this->club->members()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->filter(fn (User $member): bool => $search === '' || Str::contains(Str::lower($member->fullName()), $search))
            ->values();
    }

    /**
     * Nombre total de membres du club.
     */
    #[Computed]
    public function membersCount(): int
    {
        return $this->club->members()->count();
    }

    /**
     * Présences de chaque membre aux séances passées, pour la ligne « présent x / n ».
     *
     * @return array{sessions: int, presences: array<int, int>}
     */
    #[Computed]
    public function pastPresences(): array
    {
        return AttendanceReport::for($this->club)->pastPresences();
    }

    /**
     * Change la séance pointée.
     *
     * @param  string  $date  date proposée (AAAA-MM-JJ)
     */
    public function selectDate(string $date): void
    {
        if (in_array($date, $this->availableDates, true)) {
            $this->date = $date;
            unset($this->session, $this->presentIds);
        }
    }

    /**
     * Pointe ou dépointe un membre.
     *
     * @param  int  $userId  membre du club
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function toggle(int $userId): void
    {
        $this->authorize('manage', $this->club);

        if ($this->club->members()->whereKey($userId)->exists()) {
            $session = $this->sessionToFill();
            $is_present = $session->attendees()->whereKey($userId)->exists();

            $session->setAttendance([$userId], $is_present ? AttendanceStatus::EXPECTED : AttendanceStatus::PRESENT);
        }

        unset($this->session, $this->presentIds);
    }

    /**
     * Pointe tous les membres.
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function markAll(): void
    {
        $this->authorize('manage', $this->club);

        $this->sessionToFill()->setAttendance($this->club->members()->pluck('users.id'), AttendanceStatus::PRESENT);

        unset($this->session, $this->presentIds);
    }

    /**
     * Dépointe tout le monde ; la séance reste enregistrée, sans présent.
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function resetAttendance(): void
    {
        $this->authorize('manage', $this->club);

        $this->session?->resetAttendance();

        unset($this->session, $this->presentIds);
    }

    /**
     * Termine l'appel : retour au tableau de bord avec un toast.
     */
    public function done(): void
    {
        session()->flash('toast', trans_choice('clubs.attendance.saved', count($this->presentIds), ['count' => count($this->presentIds)]));

        $this->redirectRoute('clubs.dashboard', $this->club);
    }

    /**
     * Séance de la date choisie, créée si besoin (seules les dates proposées sont pointables).
     */
    protected function sessionToFill(): Event
    {
        abort_unless(in_array($this->date, $this->availableDates, true), 404);

        // whereDate et non firstOrCreate : scheduled_at est stocké avec une heure (00:00:00), une
        // comparaison avec la date seule ne retrouverait pas la séance existante
        return $this->club->events()->whereDate('scheduled_at', $this->date)->first()
            ?? $this->club->createCourse($this->date);
    }

    /**
     * Libellé court d'une date proposée : « Aujourd'hui », sinon jour et date (« jeu. 24/09 »).
     *
     * @param  string  $date  date (AAAA-MM-JJ)
     */
    public function dateLabel(string $date): string
    {
        return $date === $this->today()
            ? __('clubs.attendance.today')
            : CarbonImmutable::parse($date)->locale(app()->getLocale())->isoFormat(__('clubs.attendance.date_format'));
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.attendance.title', ['club' => $this->club->name]));
    }
};
?>

<x-app-shell mode="manage" :club="$club" section="attendance">
    <div class="mx-auto flex max-w-160 flex-col gap-3">
        <div class="flex flex-col gap-0.5">
            <h1 class="text-title">{{ __('clubs.attendance.heading') }}</h1>
            <span class="text-sm text-muted">{{ $club->name }} · {{ Str::ucfirst(CarbonImmutable::parse($date)->locale(app()->getLocale())->isoFormat(__('clubs.dashboard.date_format'))) }}</span>
        </div>

        <div class="flex flex-wrap gap-1.5" role="group" aria-label="{{ __('clubs.attendance.sessions') }}">
            @foreach ($this->availableDates as $available_date)
                <button type="button" class="mx-chip" aria-pressed="{{ $available_date === $date ? 'true' : 'false' }}" wire:click="selectDate('{{ $available_date }}')" wire:key="date-{{ $available_date }}">
                    {{ $this->dateLabel($available_date) }}
                </button>
            @endforeach
        </div>

        <div class="mx-card flex flex-wrap items-center justify-between gap-2.5 px-3.5 py-2.5">
            <div class="flex items-baseline gap-1.5">
                <span class="text-figure">{{ count($this->presentIds) }}</span>
                <span class="whitespace-nowrap text-muted">{{ trans_choice('clubs.attendance.present_out_of', $this->membersCount, ['total' => $this->membersCount]) }}</span>
            </div>
            <div class="flex gap-1.5">
                <button type="button" class="mx-chip" wire:click="markAll">{{ __('clubs.attendance.all_present') }}</button>
                <button type="button" class="mx-chip" wire:click="resetAttendance">{{ __('clubs.attendance.reset') }}</button>
            </div>
        </div>

        <label for="attendance-search" class="sr-only">{{ __('clubs.attendance.search') }}</label>
        <input id="attendance-search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('clubs.attendance.search') }}" class="mx-input" autocomplete="off">

        <ul class="flex flex-col">
            @foreach ($this->members as $member)
                @php($is_present = in_array($member->id, $this->presentIds, true))
                <li @class(['flex min-h-14.5 items-center gap-2.5 mx-border-b border-surface-2 px-1.5 py-2', 'bg-surface-2' => $is_present]) wire:key="member-{{ $member->id }}">
                    <span class="grid size-8.5 flex-none place-items-center mx-border bg-surface text-xs font-bold">{{ $member->initials() }}</span>
                    <div class="flex min-w-0 flex-1 flex-col">
                        <b class="truncate">{{ $member->fullName() }}</b>
                        <span class="text-xs text-muted">
                            {{ __('clubs.attendance.elo', ['elo' => Number::format($member->elo_rating)]) }}
                            @if ($this->pastPresences['sessions'] > 0)
                                · {{ __('clubs.attendance.rate', ['count' => $this->pastPresences['presences'][$member->id] ?? 0, 'total' => $this->pastPresences['sessions']]) }}
                            @endif
                        </span>
                    </div>
                    <button type="button" class="mx-chip min-w-25 flex-none justify-center font-bold" aria-pressed="{{ $is_present ? 'true' : 'false' }}" wire:click="toggle({{ $member->id }})">
                        {{ $is_present ? __('clubs.attendance.present') : __('clubs.attendance.absent') }}
                    </button>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('clubs.players.create', $club) }}" class="mx-btn mx-btn--sm">{{ __('clubs.attendance.new_student') }}</a>

        <div class="sticky bottom-0 flex flex-col gap-2 mx-border-t bg-surface pt-2.5 pb-1">
            <button type="button" class="mx-btn mx-btn--primary w-full" wire:click="done">{{ __('clubs.attendance.done') }}</button>
        </div>
    </div>
</x-app-shell>
