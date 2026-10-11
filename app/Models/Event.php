<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\User\Role;
use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Événement d'un club (cours, tournoi…), horodaté ; ses membres pointés figurent dans la table
 * attendances, avec leur rôle à l'événement (animateur, joueur, arbitre) et leur statut de présence.
 *
 * @see docs/events.md
 *
 * @property int $id
 * @property CarbonImmutable $scheduled_at
 * @property string $description
 * @property int $event_type_id
 * @property int $event_status_id
 * @property int $club_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property int|null $attendees_count
 * @property-read Club $club
 * @property-read EventType $type
 * @property-read EventStatus $status
 * @property-read Collection<int, User> $participants
 * @property-read Collection<int, User> $attendees
 * @property-read Collection<int, User> $animators
 * @property-read Collection<int, User> $players
 */
#[Fillable(['scheduled_at', 'description', 'event_type_id', 'event_status_id', 'club_id'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasMicrosecondDates;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Écrit l'horodatage de l'événement à la seconde, comme sa colonne (`precision: 0`), en dépit
     * du format à microsecondes de HasMicrosecondDates.
     *
     * @return Attribute<never, string>
     */
    protected function scheduledAt(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => $this->asDateTime($value)->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Club de l'événement.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Type de l'événement (cours, tournoi, autre).
     *
     * @return BelongsTo<EventType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Statut de l'événement.
     *
     * @return BelongsTo<EventStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(EventStatus::class, 'event_status_id');
    }

    /**
     * Membres attendus ou pointés à l'événement, avec leur rôle à l'événement (pivot role_id) et
     * leur statut de présence (pivot attendance_status_id).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendances')
            ->withPivot('role_id', 'attendance_status_id')
            ->withTimestamps();
    }

    /**
     * Membres présents à l'événement : statut compté comme présent (AttendanceStatus::ATTENDING).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function attendees(): BelongsToMany
    {
        return $this->participants()
            ->wherePivotIn('attendance_status_id', AttendanceStatus::idsFor(AttendanceStatus::ATTENDING));
    }

    /**
     * Membres qui animent l'événement (rôle Role::ANIMATOR à l'événement).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function animators(): BelongsToMany
    {
        return $this->participants()->wherePivot('role_id', Role::idFor(Role::ANIMATOR));
    }

    /**
     * Membres qui reçoivent le cours ou jouent à l'événement (rôle Role::PLAYER à l'événement).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function players(): BelongsToMany
    {
        return $this->participants()->wherePivot('role_id', Role::idFor(Role::PLAYER));
    }

    /**
     * Inscrit chaque membre du club comme attendu (AttendanceStatus::EXPECTED), avec le rôle que
     * lui donne son rôle de club ; à appeler à la création de l'événement.
     */
    public function expectMembers(): void
    {
        $this->setAttendance($this->club->members()->pluck('users.id'), AttendanceStatus::EXPECTED);
    }

    /**
     * Inscrit des membres à l'événement avec un rôle : un nouveau venu y est attendu
     * (AttendanceStatus::EXPECTED), un membre déjà inscrit y change de rôle et garde son statut de
     * présence.
     *
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     * @param  string  $role_code  code du rôle à l'événement (Role::ANIMATOR, Role::PLAYER, Role::REFEREE)
     */
    public function enrol(iterable $user_ids, string $role_code): void
    {
        $role_id = Role::idFor($role_code);
        $user_ids = collect($user_ids)->map(fn (int|string $user_id): int => (int) $user_id)->unique()->values();
        $enrolled_ids = $this->participants()->whereKey($user_ids)->pluck('users.id');

        $this->participants()->newPivotQuery()->whereIn('user_id', $enrolled_ids)->update([
            'role_id' => $role_id,
            'updated_at' => $this->freshTimestampString(),
        ]);

        $pivot = ['role_id' => $role_id, 'attendance_status_id' => AttendanceStatus::idFor(AttendanceStatus::EXPECTED)];

        $this->participants()->attach(
            $user_ids->diff($enrolled_ids)->mapWithKeys(fn (int $user_id): array => [$user_id => $pivot])->all(),
        );
    }

    /**
     * Retire des membres de l'événement, avec leur pointage.
     *
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     */
    public function withdraw(iterable $user_ids): void
    {
        $this->participants()->detach(collect($user_ids)->map(fn (int|string $user_id): int => (int) $user_id)->all());
    }

    /**
     * Indique si l'événement compte au moins un animateur et au moins un joueur, comme l'exige un
     * cours planifié.
     */
    public function isStaffed(): bool
    {
        return $this->animators()->exists() && $this->players()->exists();
    }

    /**
     * Donne un statut de présence à des membres, attendus ou non : un membre déjà inscrit garde
     * son rôle à l'événement, un nouveau venu reçoit celui de son rôle de club.
     *
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     * @param  string  $status_code  code du statut (constante de AttendanceStatus)
     */
    public function setAttendance(iterable $user_ids, string $status_code): void
    {
        $status_id = AttendanceStatus::idFor($status_code);
        $user_ids = collect($user_ids)->map(fn (int|string $user_id): int => (int) $user_id)->unique()->values();
        $enrolled_ids = $this->participants()->whereKey($user_ids)->pluck('users.id');

        $this->participants()->newPivotQuery()->whereIn('user_id', $enrolled_ids)->update([
            'attendance_status_id' => $status_id,
            'updated_at' => $this->freshTimestampString(),
        ]);

        $this->participants()->attach(
            $this->defaultRoleIds($user_ids->diff($enrolled_ids))
                ->map(fn (int $role_id): array => ['role_id' => $role_id, 'attendance_status_id' => $status_id])
                ->all(),
        );
    }

    /**
     * Remet chaque membre au statut attendu : l'événement reste enregistré, sans présent.
     */
    public function resetAttendance(): void
    {
        $this->participants()->newPivotQuery()->update([
            'attendance_status_id' => AttendanceStatus::idFor(AttendanceStatus::EXPECTED),
            'updated_at' => $this->freshTimestampString(),
        ]);
    }

    /**
     * Donne le rôle tenu par défaut à l'événement par des utilisateurs : celui de leur rôle de club
     * (Role::EVENT_ROLE_BY_CLUB_ROLE), joueur pour qui n'est pas membre du club (invité d'un
     * tournoi, par exemple).
     *
     * @param  BaseCollection<int, int>  $user_ids  identifiants des utilisateurs
     * @return BaseCollection<int, int> identifiant du rôle d'événement, indexé par identifiant d'utilisateur
     */
    protected function defaultRoleIds(BaseCollection $user_ids): BaseCollection
    {
        if ($user_ids->isEmpty()) {
            return collect();
        }

        $event_role_ids = Role::eventRoleIds();
        $club_role_ids = $this->club->members()->whereKey($user_ids)->pluck('memberships.role_id', 'users.id');
        $player_role_id = Role::idFor(Role::PLAYER);

        return $user_ids->mapWithKeys(fn (int $user_id): array => [
            $user_id => isset($club_role_ids[$user_id]) ? $event_role_ids[$club_role_ids[$user_id]] : $player_role_id,
        ]);
    }
}
