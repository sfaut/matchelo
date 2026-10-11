<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Statut d'une adhésion à un club, de la demande au départ.
 *
 * @see docs/clubs.md
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'description', 'display_order'])]
class MembershipStatus extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du statut en attente : demande d'adhésion par le code d'invitation, à valider par un
     * gestionnaire.
     *
     * @var string
     */
    public const string PENDING = 'PENDING';

    /**
     * Code du statut actif : membre du club à part entière.
     *
     * @var string
     */
    public const string ACTIVE = 'ACTIVE';

    /**
     * Code du statut suspendu : membre temporairement écarté, sans droit au club.
     *
     * @var string
     */
    public const string SUSPENDED = 'SUSPENDED';

    /**
     * Code du statut parti : ancien membre, dont l'adhésion et l'historique sont conservés.
     *
     * @var string
     */
    public const string LEFT = 'LEFT';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }

    /**
     * Trie les statuts dans leur ordre d'affichage (menus de choix), puis par identifiant.
     *
     * @param  Builder<MembershipStatus>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('id');
    }

    /**
     * Donne l'identifiant de l'enregistrement portant un code.
     *
     * @param  string  $code  code (constante de la classe, ex. MembershipStatus::ACTIVE)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }
}
