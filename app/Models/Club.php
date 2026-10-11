<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\User\Role;
use Closure;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Club d'échecs, créé par un utilisateur et réunissant des membres avec un rôle.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $city
 * @property string $description
 * @property string $logo_path
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read Collection<int, User> $members
 * @property-read Collection<int, Membership> $memberships
 * @property-read Collection<int, Event> $events
 */
#[Fillable(['name', 'code', 'city', 'description', 'logo_path', 'user_id'])]
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory, HasMicrosecondDates;

    /**
     * Génère un code d'invitation libre : deux initiales du nom (complétées par X) et quatre
     * chiffres, ex. « Échiquier Nord » → EN-1177.
     *
     * @param  string  $name  nom du club
     * @param  (Closure(): int)|null  $random_number  tirage des quatre chiffres (aléatoire par défaut)
     */
    public static function generateCode(string $name, ?Closure $random_number = null): string
    {
        $random_number ??= fn (): int => random_int(1000, 9999);

        $initials = Str::of($name)->ascii()->upper()->split('/\s+/')
            ->map(fn (string $word): string => Str::of($word)->replaceMatches('/[^A-Z]/', '')->substr(0, 1)->toString())
            ->implode('');
        $prefix = str_pad(substr($initials, 0, 2), 2, 'X');

        do {
            $code = "{$prefix}-{$random_number()}";
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * URL publique du logo, ou null sans logo (logo_path vide).
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path === '' ? null : Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Utilisateur qui a créé le club.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Événements du club : cours, tournois…
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Crée un cours du club à une date, au statut terminé (pointage encore modifiable), chaque
     * membre attendu : c'est l'événement que crée le pointage d'une séance.
     *
     * @param  string  $date  date du cours (AAAA-MM-JJ)
     */
    public function createCourse(string $date): Event
    {
        $course = $this->events()->create([
            'scheduled_at' => $date,
            'event_type_id' => EventType::idFor(EventType::COURSE),
            'event_status_id' => EventStatus::idFor(EventStatus::COMPLETED),
        ]);
        $course->expectMembers();

        return $course;
    }

    /**
     * Membres du club proposés pour un rôle d'événement : d'abord ceux dont le rôle de club y
     * conduit (Role::clubRolesFor(), ex. présidents et animateurs pour animer), puis les autres,
     * chaque groupe par ordre alphabétique.
     *
     * @param  string  $event_role_code  code du rôle d'événement (Role::ANIMATOR, Role::PLAYER, Role::REFEREE)
     * @return BelongsToMany<User, $this, Membership, 'membership'>
     */
    public function candidatesFor(string $event_role_code): BelongsToMany
    {
        $preferred_role_ids = Role::idsFor(Role::clubRolesFor($event_role_code));
        $placeholders = implode(', ', array_fill(0, count($preferred_role_ids), '?'));

        return $this->members()
            ->orderByRaw("case when memberships.role_id in ({$placeholders}) then 0 else 1 end", $preferred_role_ids)
            ->orderBy('users.last_name')
            ->orderBy('users.first_name');
    }

    /**
     * Membres du club, chacun avec son adhésion (attribut membership : rôle, date d'adhésion).
     *
     * @return BelongsToMany<User, $this, Membership, 'membership'>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->using(Membership::class)
            ->as('membership')
            ->withPivot('id', 'role_id', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Adhésions au club.
     *
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
