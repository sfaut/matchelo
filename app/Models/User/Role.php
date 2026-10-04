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
    //
}
