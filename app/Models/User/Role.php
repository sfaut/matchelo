<?php

namespace App\Models\User;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Rôle d'un membre (président, animateur, arbitre, joueur) : dans un club (memberships.role_id) ou à
 * un événement (attendances.role_id). À un événement, le rôle de
 * club donne le rôle par défaut (Role::EVENT_ROLE_BY_CLUB_ROLE).
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
     * Code du rôle d'arbitre (valide ou saisit les résultats des parties, sans gérer le club).
     *
     * @var string
     */
    public const string REFEREE = 'REFEREE';

    /**
     * Codes des rôles qui gèrent un club (mode « Gérer le club »).
     *
     * @var list<string>
     */
    public const array MANAGERS = [self::PRESIDENT, self::ANIMATOR];

    /**
     * Rôle tenu par défaut à un événement selon le rôle de club : le président y anime, les
     * autres y gardent leur rôle. Le président n'est pas un rôle d'événement.
     *
     * @var array<string, string>
     */
    public const array EVENT_ROLE_BY_CLUB_ROLE = [
        self::PRESIDENT => self::ANIMATOR,
        self::ANIMATOR => self::ANIMATOR,
        self::REFEREE => self::REFEREE,
        self::PLAYER => self::PLAYER,
    ];

    /**
     * Donne l'identifiant du rôle portant un code.
     *
     * @param  string  $code  code du rôle (Role::PRESIDENT, Role::ANIMATOR, Role::PLAYER, Role::REFEREE)
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

    /**
     * Donne, pour chaque rôle de club, l'identifiant du rôle tenu par défaut à un événement
     * (Role::EVENT_ROLE_BY_CLUB_ROLE).
     *
     * @return array<int, int> identifiant du rôle d'événement, indexé par identifiant du rôle de club
     */
    public static function eventRoleIds(): array
    {
        $ids = static::query()->pluck('id', 'code');

        return collect(self::EVENT_ROLE_BY_CLUB_ROLE)
            ->mapWithKeys(fn (string $event_role_code, string $club_role_code): array => [$ids[$club_role_code] => $ids[$event_role_code]])
            ->all();
    }

    /**
     * Donne les codes des rôles de club qui tiennent par défaut un rôle d'événement ; ex. président
     * et animateur pour Role::ANIMATOR.
     *
     * @param  string  $event_role_code  code du rôle d'événement (Role::ANIMATOR, Role::PLAYER, Role::REFEREE)
     * @return list<string>
     */
    public static function clubRolesFor(string $event_role_code): array
    {
        return array_keys(array_filter(self::EVENT_ROLE_BY_CLUB_ROLE, fn (string $code): bool => $code === $event_role_code));
    }
}
