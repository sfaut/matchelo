<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Statut d'un événement de club, du brouillon à la clôture du pointage.
 *
 * @see docs/events.md
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'description'])]
class EventStatus extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du statut brouillon (en cours de création, non visible).
     *
     * @var string
     */
    public const string DRAFT = 'DRAFT';

    /**
     * Code du statut planifié.
     *
     * @var string
     */
    public const string SCHEDULED = 'SCHEDULED';

    /**
     * Code du statut en cours.
     *
     * @var string
     */
    public const string IN_PROGRESS = 'IN_PROGRESS';

    /**
     * Code du statut terminé (pointage encore modifiable).
     *
     * @var string
     */
    public const string COMPLETED = 'COMPLETED';

    /**
     * Code du statut clôturé (pointage définitivement clos).
     *
     * @var string
     */
    public const string FINALIZED = 'FINALIZED';

    /**
     * Code du statut annulé.
     *
     * @var string
     */
    public const string CANCELLED = 'CANCELLED';

    /**
     * Donne l'identifiant de l'enregistrement portant un code.
     *
     * @param  string  $code  code (constante de la classe, ex. EventStatus::DRAFT)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }
}
