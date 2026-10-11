<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * Adhésion d'un utilisateur à un club, avec son rôle au club ; un utilisateur adhère au plus une
 * fois à chaque club (unicité club_id, user_id).
 *
 * @see docs/clubs.md
 * @see docs/roles.md
 *
 * @property int $id
 * @property int $club_id
 * @property int $user_id
 * @property int $role_id
 * @property Carbon $joined_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read User $user
 * @property-read Role $role
 */
#[Fillable(['club_id', 'user_id', 'role_id', 'joined_at'])]
class Membership extends Pivot
{
    use HasMicrosecondDates;

    /**
     * Indique que la clef primaire (id) s'incrémente, contrairement à une table pivot ordinaire.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Table des adhésions.
     *
     * @var string
     */
    protected $table = 'memberships';

    /**
     * Date l'adhésion à sa création, sauf date fournie (adhésion antérieure à l'application) :
     * l'horloge de l'application prévaut sur la valeur par défaut de la colonne.
     */
    protected static function booted(): void
    {
        static::creating(function (Membership $membership): void {
            $membership->joined_at ??= $membership->freshTimestamp();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Club de l'adhésion.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Utilisateur membre du club.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rôle du membre au club.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
