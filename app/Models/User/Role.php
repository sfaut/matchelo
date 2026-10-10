<?php

namespace App\Models\User;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Rôle d'un membre dans un club (président, animateur, joueur), porté par club_user.role_id.
 *
 * @see docs/roles.md
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'description'])]
class Role extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du rôle de président (dirige le club et ses animateurs).
     *
     * @var string
     */
    public const string PRESIDENT = 'PRESIDENT';

    /**
     * Code du rôle d'animateur (prof qui gère le club).
     *
     * @var string
     */
    public const string ANIMATOR = 'ANIMATOR';

    /**
     * Code du rôle de joueur.
     *
     * @var string
     */
    public const string PLAYER = 'PLAYER';

    /**
     * Codes des rôles qui gèrent un club (mode « Gérer le club »).
     *
     * @var list<string>
     */
    public const array MANAGERS = [self::PRESIDENT, self::ANIMATOR];

    /**
     * Donne l'identifiant du rôle portant un code.
     *
     * @param  string  $code  code du rôle (Role::PRESIDENT, Role::ANIMATOR, Role::PLAYER)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }

    /**
     * Donne les identifiants des rôles portant des codes.
     *
     * @param  list<string>  $codes  codes des rôles (ex. Role::MANAGERS)
     * @return list<int>
     */
    public static function idsFor(array $codes): array
    {
        return array_values(static::query()->whereIn('code', $codes)->get()->map(fn (Role $role): int => $role->id)->all());
    }
}
