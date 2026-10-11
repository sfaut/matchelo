<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\Concerns\RecordsChanges;
use App\Models\User\Role;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Adhésion d'un utilisateur à un club, avec son rôle au club et son statut, tous deux historisés
 * (membership_role_changes, membership_status_changes) ; un utilisateur adhère au plus une fois à chaque club (unicité
 * club_id, user_id).
 *
 * @see docs/clubs.md
 * @see docs/roles.md
 *
 * @property int $id
 * @property int $club_id
 * @property int $user_id
 * @property int $role_id
 * @property int $membership_status_id
 * @property Carbon $joined_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read User $user
 * @property-read Role $role
 * @property-read MembershipStatus $status
 * @property-read Collection<int, MembershipStatusChange> $statusChanges
 * @property-read Collection<int, MembershipRoleChange> $roleChanges
 */
#[Fillable(['club_id', 'user_id', 'role_id', 'membership_status_id', 'joined_at'])]
class Membership extends Pivot
{
    use HasMicrosecondDates;
    use RecordsChanges;

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
     * À la création : adhésion active sauf statut fourni (demande en attente), datée du jour sauf
     * date fournie (adhésion antérieure à l'application, dont le statut et le rôle initiaux
     * s'historisent alors à cette date). L'horloge de l'application prévaut sur la valeur par
     * défaut de la colonne.
     */
    protected static function booted(): void
    {
        static::creating(function (Membership $membership): void {
            $membership->membership_status_id ??= MembershipStatus::idFor(MembershipStatus::ACTIVE);

            if ($membership->getAttribute('joined_at') === null) {
                $membership->joined_at = $membership->freshTimestamp();
            } else {
                $membership->recordedChangeAt = $membership->joined_at;
            }
        });

        static::created(function (Membership $membership): void {
            $membership->recordedChangeAt = null;
        });
    }

    /**
     * Écrit la date d'adhésion sans heure, comme sa colonne (`date`), en dépit du format à
     * microsecondes de HasMicrosecondDates.
     *
     * @return Attribute<never, string>
     */
    protected function joinedAt(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => $this->asDateTime($value)->format('Y-m-d'),
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
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

    /**
     * Statut courant de l'adhésion.
     *
     * @return BelongsTo<MembershipStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(MembershipStatus::class, 'membership_status_id');
    }

    /**
     * Historique des statuts de l'adhésion, du plus ancien au plus récent.
     *
     * @return HasMany<MembershipStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(MembershipStatusChange::class, 'membership_id')->orderBy('changed_at')->orderBy('id');
    }

    /**
     * Historique des rôles du membre au club, du plus ancien au plus récent.
     *
     * @return HasMany<MembershipRoleChange, $this>
     */
    public function roleChanges(): HasMany
    {
        return $this->hasMany(MembershipRoleChange::class, 'membership_id')->orderBy('changed_at')->orderBy('id');
    }

    /**
     * Change le statut de l'adhésion et l'historise ; sans effet si le statut ne change pas.
     *
     * @param  string  $status_code  code du statut (constante de MembershipStatus)
     * @param  CarbonInterface|string|null  $changed_at  horodatage du changement, à défaut l'instant présent
     */
    public function changeStatus(string $status_code, CarbonInterface|string|null $changed_at = null): void
    {
        $this->changeRecorded('membership_status_id', MembershipStatus::idFor($status_code), $changed_at);
    }

    /**
     * Change le rôle du membre au club et l'historise ; sans effet si le rôle ne change pas.
     *
     * @param  string  $role_code  code du rôle (constante de Role)
     * @param  CarbonInterface|string|null  $changed_at  horodatage du changement, à défaut l'instant présent
     */
    public function changeRole(string $role_code, CarbonInterface|string|null $changed_at = null): void
    {
        $this->changeRecorded('role_id', Role::idFor($role_code), $changed_at);
    }

    /**
     * Colonnes historisées : statut et rôle.
     *
     * @return list<string>
     */
    protected function recordedColumns(): array
    {
        return ['membership_status_id', 'role_id'];
    }

    /**
     * Historique d'une colonne historisée.
     *
     * @param  string  $column  colonne historisée (recordedColumns())
     * @return HasMany<MembershipStatusChange, $this>|HasMany<MembershipRoleChange, $this>
     *
     * @throws InvalidArgumentException pour une colonne non historisée
     */
    protected function historyOf(string $column): HasMany
    {
        return match ($column) {
            'membership_status_id' => $this->statusChanges(),
            'role_id' => $this->roleChanges(),
            default => throw new InvalidArgumentException("Colonne non historisée : {$column}"),
        };
    }

    /**
     * Auteur d'un changement fait sans utilisateur connecté : le créateur du club.
     */
    protected function fallbackChangeAuthorId(): int
    {
        return $this->club->user_id;
    }
}
