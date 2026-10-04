<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Civilité (Madame, Monsieur…), rattachée à un sexe. Libellé affiché : lang/{locale}/civilities.php, clef = code.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $name_short
 * @property int $sex_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sex $sex
 * @property-read Collection<int, User> $users
 */
#[Fillable(['code', 'name', 'name_short', 'sex_id'])]
class Civility extends Model
{
    /**
     * Sexe associé à la civilité.
     *
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class);
    }

    /**
     * Utilisateurs portant cette civilité.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
