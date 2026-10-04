<?php

namespace App\Models;

use App\Models\User\Civility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Compte d'un utilisateur : élève, prof animateur ou administrateur.
 *
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property int $civility_id
 * @property bool $is_administrator
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password_hash
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Civility $civility
 * @property-read Collection<int, Club> $createdClubs
 * @property-read Collection<int, Club> $clubs
 */
#[Fillable(['last_name', 'first_name', 'civility_id', 'email', 'password_hash'])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable;

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
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Civilité de l'utilisateur.
     *
     * @return BelongsTo<Civility, $this>
     */
    public function civility(): BelongsTo
    {
        return $this->belongsTo(Civility::class);
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
     * @return BelongsToMany<Club, $this>
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Initiales de l'utilisateur : prénom puis nom.
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->first_name, 0, 1).Str::substr($this->last_name, 0, 1));
    }
}
