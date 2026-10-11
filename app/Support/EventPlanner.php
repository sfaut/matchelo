<?php

namespace App\Support;

use App\Models\Club;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User\Role;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Planification en masse des événements d'un club : génération d'événements indépendants sur des
 * jours de la semaine, modification et suppression d'une sélection, composition (inscription,
 * changement de rôle, retrait, reprise des participants d'un autre événement).
 *
 * Chaque opération porte sur des événements du club et s'exécute en une transaction : un cours
 * qui y perdrait son dernier animateur ou son dernier joueur annule toute l'opération.
 *
 * @see docs/events.md
 */
class EventPlanner
{
    /**
     * @param  Club  $club  club dont on planifie les événements
     */
    public function __construct(
        private readonly Club $club,
    ) {
        //
    }

    /**
     * Horodatages des jours de la semaine choisis entre deux dates incluses, à une heure donnée :
     * l'aperçu à présenter avant generate(), pour en retirer des dates (vacances…).
     *
     * @param  string  $from  première date (AAAA-MM-JJ)
     * @param  string  $until  dernière date, incluse (AAAA-MM-JJ)
     * @param  list<int>  $weekdays  jours de la semaine (ISO : 1 = lundi … 7 = dimanche)
     * @param  string  $time  heure (HH:MM)
     * @return list<CarbonImmutable>
     */
    public function occurrences(string $from, string $until, array $weekdays, string $time): array
    {
        $occurrences = [];

        foreach (CarbonPeriodImmutable::create($from, $until) as $day) {
            if (in_array($day->isoWeekday(), $weekdays, true)) {
                $occurrences[] = $day->setTimeFromTimeString($time);
            }
        }

        return $occurrences;
    }

    /**
     * Génère des événements indépendants et planifiés, un par horodatage, chacun avec ses
     * animateurs et ses joueurs attendus.
     *
     * @param  iterable<CarbonImmutable|string>  $dates  horodatages des événements
     * @param  string  $type_code  code du type (constante de EventType)
     * @param  iterable<int|string>  $animator_ids  identifiants des animateurs
     * @param  iterable<int|string>  $player_ids  identifiants des joueurs
     * @param  string  $description  description commune
     * @return Collection<int, Event>
     *
     * @throws InvalidArgumentException si un cours n'a pas d'animateur ou pas de joueur
     */
    public function generate(iterable $dates, string $type_code, iterable $animator_ids, iterable $player_ids, string $description = ''): Collection
    {
        $animator_ids = collect($animator_ids);
        $player_ids = collect($player_ids);

        return DB::transaction(function () use ($dates, $type_code, $animator_ids, $player_ids, $description): Collection {
            $events = new Collection;

            foreach ($dates as $date) {
                $event = $this->club->events()->create([
                    'scheduled_at' => $date,
                    'description' => $description,
                    'event_type_id' => EventType::idFor($type_code),
                    'event_status_id' => EventStatus::idFor(EventStatus::SCHEDULED),
                ]);
                $event->enrol($animator_ids, Role::ANIMATOR);
                $event->enrol($player_ids, Role::PLAYER);
                $events->push($event);
            }

            $this->ensureCoursesStaffed($events);

            return $events;
        });
    }

    /**
     * Modifie des événements du club ; un paramètre laissé à null garde la valeur en place.
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @param  string|null  $time  nouvelle heure (HH:MM), chaque événement gardant sa date
     * @param  string|null  $status_code  nouveau statut (constante de EventStatus)
     * @param  string|null  $description  nouvelle description
     * @return Collection<int, Event>
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     */
    public function update(iterable $event_ids, ?string $time = null, ?string $status_code = null, ?string $description = null): Collection
    {
        $events = $this->events($event_ids);
        $status_id = $status_code === null ? null : EventStatus::idFor($status_code);

        DB::transaction(function () use ($events, $time, $status_id, $description): void {
            foreach ($events as $event) {
                if ($time !== null) {
                    $event->scheduled_at = $event->scheduled_at->setTimeFromTimeString($time);
                }

                $event->event_status_id = $status_id ?? $event->event_status_id;
                $event->description = $description ?? $event->description;
                $event->save();
            }
        });

        return $events;
    }

    /**
     * Supprime des événements du club, avec leur pointage.
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @return int nombre d'événements supprimés
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     */
    public function delete(iterable $event_ids): int
    {
        $events = $this->events($event_ids);

        return $this->club->events()->whereKey($events->modelKeys())->delete();
    }

    /**
     * Inscrit des membres à des événements avec un rôle ; un membre déjà inscrit y change de rôle
     * et garde son statut de présence (Event::enrol()).
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     * @param  string  $role_code  code du rôle à l'événement (Role::ANIMATOR, Role::PLAYER, Role::REFEREE)
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     * @throws InvalidArgumentException si un événement est clôturé, ou si un cours perd son dernier animateur ou joueur
     */
    public function enrol(iterable $event_ids, iterable $user_ids, string $role_code): void
    {
        $user_ids = collect($user_ids);

        $this->compose($event_ids, function (Event $event) use ($user_ids, $role_code): void {
            $event->enrol($user_ids, $role_code);
        });
    }

    /**
     * Retire des membres d'événements, avec leur pointage.
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     * @throws InvalidArgumentException si un événement est clôturé, ou si un cours perd son dernier animateur ou joueur
     */
    public function withdraw(iterable $event_ids, iterable $user_ids): void
    {
        $user_ids = collect($user_ids);

        $this->compose($event_ids, function (Event $event) use ($user_ids): void {
            $event->withdraw($user_ids);
        });
    }

    /**
     * Reprend les participants d'un événement du club, avec leur rôle, dans d'autres événements ;
     * les participants déjà en place y restent.
     *
     * @param  Event  $source  événement dont on reprend les participants
     * @param  iterable<int|string>  $event_ids  identifiants des événements à composer
     *
     * @throws ModelNotFoundException si un événement, source compris, n'appartient pas au club
     * @throws InvalidArgumentException si un événement est clôturé
     */
    public function copyParticipants(Event $source, iterable $event_ids): void
    {
        $this->events([$source->id]);

        $role_codes = Role::query()->pluck('code', 'id');
        $user_ids_by_role = $source->participants()->pluck('attendances.role_id', 'users.id')
            ->mapToGroups(fn (int $role_id, int $user_id): array => [$role_codes[$role_id] => $user_id]);

        $this->compose($event_ids, function (Event $event) use ($user_ids_by_role): void {
            foreach ($user_ids_by_role as $role_code => $user_ids) {
                $event->enrol($user_ids, $role_code);
            }
        });
    }

    /**
     * Applique un changement de composition à des événements du club, en une transaction, puis
     * vérifie que chaque cours garde au moins un animateur et un joueur.
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @param  callable(Event): void  $change  changement à appliquer à chaque événement
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     * @throws InvalidArgumentException si un événement est clôturé, ou si un cours perd son dernier animateur ou joueur
     */
    protected function compose(iterable $event_ids, callable $change): void
    {
        $events = $this->events($event_ids);
        $finalized_id = EventStatus::idFor(EventStatus::FINALIZED);

        if ($events->contains(fn (Event $event): bool => (int) $event->event_status_id === $finalized_id)) {
            throw new InvalidArgumentException('Le pointage d\'un événement clôturé ne se modifie plus.');
        }

        DB::transaction(function () use ($events, $change): void {
            $events->each($change);
            $this->ensureCoursesStaffed($events);
        });
    }

    /**
     * Vérifie que chaque cours compte au moins un animateur et au moins un joueur.
     *
     * @param  Collection<int, Event>  $events  événements à vérifier
     *
     * @throws InvalidArgumentException au premier cours incomplet
     */
    protected function ensureCoursesStaffed(Collection $events): void
    {
        $course_type_id = EventType::idFor(EventType::COURSE);

        foreach ($events as $event) {
            if ((int) $event->event_type_id === $course_type_id && ! $event->isStaffed()) {
                throw new InvalidArgumentException('Un cours compte au moins un animateur et au moins un joueur.');
            }
        }
    }

    /**
     * Charge des événements du club.
     *
     * @param  iterable<int|string>  $event_ids  identifiants des événements
     * @return Collection<int, Event>
     *
     * @throws ModelNotFoundException si un événement n'appartient pas au club
     */
    protected function events(iterable $event_ids): Collection
    {
        $event_ids = collect($event_ids)->map(fn (int|string $event_id): int => (int) $event_id)->unique()->values();
        $events = $this->club->events()->whereKey($event_ids)->get();

        if ($events->count() !== $event_ids->count()) {
            throw (new ModelNotFoundException)->setModel(Event::class, $event_ids->diff($events->modelKeys())->values()->all());
        }

        return $events;
    }
}
