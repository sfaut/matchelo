<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\Concerns\RecordsStatusChanges;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * Pointage d'un participant à un événement : son rôle à l'événement et son statut de présence,
 * historisé (attendance_status_changes). Un utilisateur est pointé au plus une fois par
 * événement (unicité event_id, user_id).
 *
 * @see docs/events.md
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property int $role_id
 * @property int $attendance_status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read User $user
 * @property-read Role $role
 * @property-read AttendanceStatus $status
 */
#[Fillable(['event_id', 'user_id', 'role_id', 'attendance_status_id'])]
class Attendance extends Pivot
{
    use HasMicrosecondDates;

    /** @use RecordsStatusChanges<AttendanceStatusChange> */
    use RecordsStatusChanges;

    /**
     * Indique que la clef primaire (id) s'incrémente, contrairement à une table pivot ordinaire.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Table du pointage.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * Événement du pointage.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Participant pointé.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rôle tenu à l'événement.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Statut de présence courant.
     *
     * @return BelongsTo<AttendanceStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    /**
     * Historique des statuts de présence, du plus ancien au plus récent.
     *
     * @return HasMany<AttendanceStatusChange, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(AttendanceStatusChange::class, 'attendance_id')->orderBy('changed_at')->orderBy('id');
    }

    /**
     * Nom de la colonne de statut, commun au pointage et à son historique.
     */
    protected function statusColumn(): string
    {
        return 'attendance_status_id';
    }

    /**
     * Donne l'identifiant d'un statut de présence d'après son code.
     *
     * @param  string  $status_code  code du statut (constante de AttendanceStatus)
     */
    protected function statusIdFor(string $status_code): ?int
    {
        return AttendanceStatus::idFor($status_code);
    }

    /**
     * Auteur d'un changement fait sans utilisateur connecté : le créateur du club.
     */
    protected function fallbackStatusAuthorId(): int
    {
        return $this->event->club->user_id;
    }
}
