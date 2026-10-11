<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Changement de statut d'un événement, horodaté et attribué à son auteur ; le premier enregistre
 * le statut initial.
 *
 * @see docs/events.md
 *
 * @property int $id
 * @property int $event_id
 * @property int $event_status_id
 * @property Carbon $changed_at
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read EventStatus $status
 * @property-read User $author
 */
#[Fillable(['event_id', 'event_status_id', 'changed_at', 'user_id'])]
class EventStatusChange extends Model
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
            'changed_at' => 'datetime',
        ];
    }

    /**
     * Événement concerné.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Statut pris par l'événement.
     *
     * @return BelongsTo<EventStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(EventStatus::class, 'event_status_id');
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
