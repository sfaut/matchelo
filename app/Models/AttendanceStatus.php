<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Statut de présence d'un membre pointé à un événement (présent, absent, excusé).
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
class AttendanceStatus extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du statut présent.
     *
     * @var string
     */
    public const string PRESENT = 'PRESENT';

    /**
     * Code du statut absent.
     *
     * @var string
     */
    public const string ABSENT = 'ABSENT';

    /**
     * Code du statut absent excusé.
     *
     * @var string
     */
    public const string EXCUSED = 'EXCUSED';

    /**
     * Donne l'identifiant de l'enregistrement portant un code.
     *
     * @param  string  $code  code (constante de la classe, ex. AttendanceStatus::PRESENT)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }
}
