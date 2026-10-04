<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Sexe, porté par les civilités.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_feminine
 * @property bool $is_masculine
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Civility> $civilities
 */
#[Fillable(['code', 'name', 'is_feminine', 'is_masculine'])]
class Sex extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_feminine' => 'boolean',
            'is_masculine' => 'boolean',
        ];
    }

    /**
     * Civilités associées à ce sexe.
     *
     * @return HasMany<Civility, $this>
     */
    public function civilities(): HasMany
    {
        return $this->hasMany(Civility::class);
    }
}
