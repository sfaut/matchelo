<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Changement de statut de présence d'un pointage, horodaté et attribué à son auteur ; le premier
 * enregistre le statut initial.
 *
 * @see docs/events.md
 *
 * @property int $id
 * @property int $attendance_id
 * @property int $attendance_status_id
 * @property Carbon $changed_at
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Attendance $attendance
 * @property-read AttendanceStatus $status
 * @property-read User $author
 */
#[Fillable(['attendance_id', 'attendance_status_id', 'changed_at', 'user_id'])]
class AttendanceStatusChange extends Model
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
     * Pointage concerné.
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Statut de présence pris.
     *
     * @return BelongsTo<AttendanceStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
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
