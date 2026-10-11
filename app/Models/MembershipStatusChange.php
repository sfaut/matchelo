<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Changement de statut d'une adhésion, daté et attribué à son auteur ; le premier enregistre le
 * statut initial.
 *
 * @see docs/clubs.md
 *
 * @property int $id
 * @property int $membership_id
 * @property int $membership_status_id
 * @property Carbon $changed_at
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Membership $membership
 * @property-read MembershipStatus $status
 * @property-read User $author
 */
#[Fillable(['membership_id', 'membership_status_id', 'changed_at', 'user_id'])]
class MembershipStatusChange extends Model
{
    use HasMicrosecondDates;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changed_at' => 'date',
        ];
    }

    /**
     * Écrit la date du changement sans heure, comme sa colonne (`date`), en dépit du format à
     * microsecondes de HasMicrosecondDates.
     *
     * @return Attribute<never, string>
     */
    protected function changedAt(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): string => $this->asDateTime($value)->format('Y-m-d'),
        );
    }

    /**
     * Adhésion concernée.
     *
     * @return BelongsTo<Membership, $this>
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Statut pris par l'adhésion.
     *
     * @return BelongsTo<MembershipStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(MembershipStatus::class, 'membership_status_id');
    }

    /**
     * Auteur du changement.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
