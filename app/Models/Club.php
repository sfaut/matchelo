<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Club d'échecs, créé par un utilisateur et réunissant des membres avec un rôle.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $city
 * @property string|null $description
 * @property string|null $logo_path
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read Collection<int, User> $members
 */
#[Fillable(['name', 'code', 'city', 'description', 'logo_path', 'user_id'])]
class Club extends Model
{
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
     * URL publique du logo, ou null sans logo.
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path === null ? null : Storage::disk('public')->url($this->logo_path);
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
     * Membres du club, avec leur rôle (club_user.role_id).
     *
     * @return BelongsToMany<User, $this, Pivot, 'pivot'>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id')
            ->withTimestamps();
    }
}
