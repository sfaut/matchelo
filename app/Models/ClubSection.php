<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Database\Factories\ClubSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Section d'un club (ex. « Section jeunes », « Section mardi »), regroupant certains de ses
 * joueurs. Un club en compte au moins une : la section générale, créée avec lui et que le
 * président peut renommer.
 *
 * @property int $id
 * @property int $club_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read Collection<int, User> $players
 */
#[Fillable(['club_id', 'name'])]
class ClubSection extends Model
{
    /** @use HasFactory<ClubSectionFactory> */
    use HasFactory, HasMicrosecondDates;

    /**
     * Club de la section.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Joueurs de la section.
     *
     * @return BelongsToMany<User, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
