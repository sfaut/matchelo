<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Rôle d'un membre dans un club (animateur, joueur), porté par club_user.role_id.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name'])]
class Role extends Model
{
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
     * Donne l'identifiant du rôle portant un code.
     *
     * @param  string  $code  code du rôle (Role::ANIMATOR, Role::PLAYER)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }
}
