<?php

namespace App\Support;

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Assiduité d'un club : séance du jour, dernières séances et taux de présence, membres absents
 * plusieurs fois de suite. Les taux se rapportent aux membres actuels du club.
 */
class AttendanceReport
{
    /**
     * @param  Club  $club  club étudié
     * @param  CarbonImmutable  $today  date du jour (début de journée)
     */
    public function __construct(
        private readonly Club $club,
        private readonly CarbonImmutable $today,
    ) {
        //
    }

    /**
     * Rapport d'un club à la date du jour.
     */
    public static function for(Club $club): self
    {
        return new self($club, CarbonImmutable::today());
    }

    /**
     * Séance du jour, avec son nombre de présents, ou null s'il n'y en a pas encore.
     */
    public function todaySession(): ?ClubSession
    {
        return $this->club->sessions()
            ->withCount('attendees')
            ->whereDate('held_on', $this->today)
            ->first();
    }

    /**
     * Dernières séances jusqu'à aujourd'hui inclus, de la plus ancienne à la plus récente, avec
     * leur nombre de présents.
     *
     * @param  int  $count  nombre de séances
     * @return Collection<int, ClubSession>
     */
    public function recentSessions(int $count): Collection
    {
        return $this->club->sessions()
            ->withCount('attendees')
            ->whereDate('held_on', '<=', $this->today)
            ->orderByDesc('held_on')
            ->limit($count)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Taux de présence d'une séance, en pourcentage arrondi.
     *
     * @param  ClubSession  $session  séance chargée avec attendees_count
     * @param  int  $members_count  nombre de membres du club
     */
    public function rate(ClubSession $session, int $members_count): int
    {
        return $members_count === 0 ? 0 : (int) round(100 * ($session->attendees_count ?? 0) / $members_count);
    }

    /**
     * Taux de présence moyen de séances, en pourcentage arrondi, ou null sans séance.
     *
     * @param  Collection<int, ClubSession>  $sessions  séances chargées avec attendees_count
     * @param  int  $members_count  nombre de membres du club
     */
    public function averageRate(Collection $sessions, int $members_count): ?int
    {
        if ($sessions->isEmpty()) {
            return null;
        }

        return (int) round($sessions->avg(fn (ClubSession $session): int => $this->rate($session, $members_count)));
    }

    /**
     * Membres absents aux dernières séances passées (avant aujourd'hui) au moins $minimum fois de
     * suite, les plus absents d'abord.
     *
     * @param  int  $minimum  nombre minimal d'absences consécutives
     * @param  int  $limit  nombre maximal de membres renvoyés
     * @return BaseCollection<int, array{user: User, absences: non-negative-int}>
     */
    public function consecutiveAbsences(int $minimum = 2, int $limit = 5): BaseCollection
    {
        $past_sessions = $this->club->sessions()
            ->with('attendees:id')
            ->whereDate('held_on', '<', $this->today)
            ->orderByDesc('held_on')
            ->get();

        return $this->club->members()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn (User $member): array => [
                'user' => $member,
                'absences' => $past_sessions
                    ->takeWhile(fn (ClubSession $session): bool => ! $session->attendees->contains($member))
                    ->count(),
            ])
            ->filter(fn (array $row): bool => $row['absences'] >= $minimum)
            ->sortByDesc('absences')
            ->take($limit)
            ->values();
    }

    /**
     * Présences de chaque membre aux séances passées (avant aujourd'hui).
     *
     * @return array{sessions: int, presences: array<int, int>}
     *                                                          nombre de séances passées, et nombre de présences par identifiant de membre
     */
    public function pastPresences(): array
    {
        $past_sessions = $this->club->sessions()
            ->with('attendees:id')
            ->whereDate('held_on', '<', $this->today)
            ->get();

        $presences = $past_sessions
            ->flatMap(fn (ClubSession $session): BaseCollection => $session->attendees->pluck('id'))
            ->countBy()
            ->all();

        return ['sessions' => $past_sessions->count(), 'presences' => $presences];
    }
}
