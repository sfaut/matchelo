<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Type d'un événement de club (cours, tournoi, autre).
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
class EventType extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du type cours (séance habituelle du club).
     *
     * @var string
     */
    public const string COURSE = 'COURSE';

    /**
     * Code du type tournoi.
     *
     * @var string
     */
    public const string TOURNAMENT = 'TOURNAMENT';

    /**
     * Code du type autre événement.
     *
     * @var string
     */
    public const string OTHER = 'OTHER';

    /**
     * Donne l'identifiant de l'enregistrement portant un code.
     *
     * @param  string  $code  code (constante de la classe, ex. EventType::COURSE)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }
}
