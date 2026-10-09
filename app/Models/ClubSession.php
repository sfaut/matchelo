<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Carbon\CarbonImmutable;
use Database\Factories\ClubSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Séance d'un club, horodatée ; ses présents sont les membres pointés (table attendances).
 *
 * @property int $id
 * @property int $club_id
 * @property CarbonImmutable $scheduled_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property int|null $attendees_count
 * @property-read Club $club
 * @property-read Collection<int, User> $attendees
 */
#[Fillable(['club_id', 'scheduled_at'])]
class ClubSession extends Model
{
    /** @use HasFactory<ClubSessionFactory> */
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
     * Écrit l'horodatage de la séance à la seconde, comme sa colonne (`precision: 0`), en dépit
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
     * Club de la séance.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Membres présents à la séance.
     *
     * @return BelongsToMany<User, $this>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendances')->withTimestamps();
    }
}
