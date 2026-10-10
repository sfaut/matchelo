<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use App\Models\User\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Compte d'un utilisateur : joueur, animateur ou président d'un club, ou administrateur.
 *
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property bool $is_administrator
 * @property int $elo_rating
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password_hash
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Club> $createdClubs
 * @property-read Collection<int, Club> $clubs
 * @property-read Collection<int, ClubSection> $clubSections
 */
#[Fillable(['last_name', 'first_name', 'elo_rating', 'email', 'password_hash'])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasMicrosecondDates, Notifiable;

    /**
     * Colonne du mot de passe haché, lue par l'authentification.
     *
     * @var string
     */
    protected $authPasswordName = 'password_hash';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_administrator' => 'boolean',
            'elo_rating' => 'integer',
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Clubs créés par l'utilisateur.
     *
     * @return HasMany<Club, $this>
     */
    public function createdClubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }

    /**
     * Clubs dont l'utilisateur est membre, avec son rôle (club_user.role_id).
     *
     * @return BelongsToMany<Club, $this, Pivot, 'pivot'>
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Sections de club dont l'utilisateur est joueur.
     *
     * @return BelongsToMany<ClubSection, $this>
     */
    public function clubSections(): BelongsToMany
    {
        return $this->belongsToMany(ClubSection::class)->withTimestamps();
    }

    /**
     * Clubs que l'utilisateur gère, comme président ou animateur (Role::MANAGERS).
     *
     * @return BelongsToMany<Club, $this, Pivot, 'pivot'>
     */
    public function managedClubs(): BelongsToMany
    {
        return $this->clubs()->wherePivotIn('role_id', Role::idsFor(Role::MANAGERS));
    }

    /**
     * Indique si l'utilisateur gère au moins un club (président ou animateur) : il a alors
     * accès au mode « Gérer le club ».
     */
    public function isManager(): bool
    {
        return $this->managedClubs()->exists();
    }

    /**
     * Indique si l'utilisateur est président d'un club donné.
     *
     * @param  Club  $club  club concerné
     */
    public function presides(Club $club): bool
    {
        return $this->clubs()
            ->whereKey($club->id)
            ->wherePivot('role_id', Role::idFor(Role::PRESIDENT))
            ->exists();
    }

    /**
     * Indique si le joueur a un compte (un e-mail pour se connecter) ; un joueur créé par un
     * prof n'en a pas.
     */
    public function hasAccount(): bool
    {
        return $this->email !== null;
    }

    /**
     * Prénom et nom de l'utilisateur.
     */
    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Initiales de l'utilisateur : prénom puis nom.
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->first_name, 0, 1).Str::substr($this->last_name, 0, 1));
    }
}
