<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\User\Role;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Section d'un club (ex. « Section jeunes », « Section mardi »), regroupant certains de ses
 * membres, chacun avec un rôle dans la section (section_user.role_id). Un club en compte au
 * moins une : la section générale, créée avec lui et que le président peut renommer.
 *
 * @see docs/clubs.md
 *
 * @property int $id
 * @property string $name
 * @property int $club_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read Collection<int, User> $players
 */
#[Fillable(['name', 'club_id'])]
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
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
     * Joueurs de la section : membres au rôle Role::PLAYER, qu'un attach() leur donne d'office.
     *
     * @return BelongsToMany<User, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivotValue('role_id', Role::idFor(Role::PLAYER))
            ->withTimestamps();
    }
}
