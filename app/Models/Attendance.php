<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\Concerns\RecordsChanges;
use App\Models\User\Role;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Pointage d'un participant à un événement : son rôle à l'événement et son statut de présence,
 * tous deux historisés (attendance_role_changes, attendance_status_changes). Un utilisateur est pointé au plus une fois par
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
 * @property-read Collection<int, AttendanceStatusChange> $statusChanges
 * @property-read Collection<int, AttendanceRoleChange> $roleChanges
 */
#[Fillable(['event_id', 'user_id', 'role_id', 'attendance_status_id'])]
class Attendance extends Pivot
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
     * Historique des rôles du participant à l'événement, du plus ancien au plus récent.
     *
     * @return HasMany<AttendanceRoleChange, $this>
     */
    public function roleChanges(): HasMany
    {
        return $this->hasMany(AttendanceRoleChange::class, 'attendance_id')->orderBy('changed_at')->orderBy('id');
    }

    /**
     * Change le statut de présence et l'historise ; sans effet si le statut ne change pas.
     *
     * @param  string  $status_code  code du statut (constante de AttendanceStatus)
     * @param  CarbonInterface|string|null  $changed_at  horodatage du changement, à défaut l'instant présent
     */
    public function changeStatus(string $status_code, CarbonInterface|string|null $changed_at = null): void
    {
        $this->changeRecorded('attendance_status_id', AttendanceStatus::idFor($status_code), $changed_at);
    }

    /**
     * Change le rôle du participant à l'événement et l'historise ; sans effet si le rôle ne change pas.
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
        return ['attendance_status_id', 'role_id'];
    }

    /**
     * Historique d'une colonne historisée.
     *
     * @param  string  $column  colonne historisée (recordedColumns())
     * @return HasMany<AttendanceStatusChange, $this>|HasMany<AttendanceRoleChange, $this>
     *
     * @throws InvalidArgumentException pour une colonne non historisée
     */
    protected function historyOf(string $column): HasMany
    {
        return match ($column) {
            'attendance_status_id' => $this->statusChanges(),
            'role_id' => $this->roleChanges(),
            default => throw new InvalidArgumentException("Colonne non historisée : {$column}"),
        };
    }

    /**
     * Auteur d'un changement fait sans utilisateur connecté : le créateur du club.
     */
    protected function fallbackChangeAuthorId(): int
    {
        return $this->event->club->user_id;
    }
}
