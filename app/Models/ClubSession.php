<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Séance d'un club à une date ; ses présents sont les membres pointés (table attendances).
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
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'date',
        ];
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
