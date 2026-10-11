<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
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

/**
 * Événement d'un club (cours, tournoi…), horodaté ; ses membres pointés figurent dans la table
 * attendances, avec leur statut de présence.
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
     * Membres attendus ou pointés à l'événement, avec leur statut de présence
     * (pivot attendance_status_id).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendances')
            ->withPivot('attendance_status_id')
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
     * Inscrit chaque membre du club comme attendu (AttendanceStatus::EXPECTED) ; à appeler à la
     * création de l'événement.
     */
    public function expectMembers(): void
    {
        $this->setAttendance($this->club->members()->pluck('users.id'), AttendanceStatus::EXPECTED);
    }

    /**
     * Donne un statut de présence à des membres, attendus ou non.
     *
     * @param  iterable<int|string>  $user_ids  identifiants des membres
     * @param  string  $status_code  code du statut (constante de AttendanceStatus)
     */
    public function setAttendance(iterable $user_ids, string $status_code): void
    {
        $status_id = AttendanceStatus::idFor($status_code);

        $this->participants()->syncWithoutDetaching(
            collect($user_ids)->mapWithKeys(fn (int|string $user_id): array => [(int) $user_id => ['attendance_status_id' => $status_id]])->all(),
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
}
