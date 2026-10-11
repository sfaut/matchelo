<?php

namespace App\Models;

use App\Models\Concerns\HasMicrosecondDates;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Statut de présence d'un membre à un événement : prévu à la création de l'événement, puis
 * présent, absent, excusé ou en retard une fois pointé.
 *
 * @see docs/events.md
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
class AttendanceStatus extends Model
{
    use HasMicrosecondDates;

    /**
     * Code du statut prévu : donné à chaque membre à la création de l'événement, avant pointage.
     *
     * @var string
     */
    public const string EXPECTED = 'EXPECTED';

    /**
     * Code du statut présent.
     *
     * @var string
     */
    public const string PRESENT = 'PRESENT';

    /**
     * Code du statut absent non excusé.
     *
     * @var string
     */
    public const string ABSENT = 'ABSENT';

    /**
     * Code du statut absent avec excuse acceptée.
     *
     * @var string
     */
    public const string EXCUSED = 'EXCUSED';

    /**
     * Code du statut arrivé en retard.
     *
     * @var string
     */
    public const string LATE = 'LATE';

    /**
     * Codes des statuts comptés comme présents (taux de présence, assiduité).
     *
     * @var list<string>
     */
    public const array ATTENDING = [self::PRESENT, self::LATE];

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
     * @param  Builder<AttendanceStatus>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('id');
    }

    /**
     * Donne l'identifiant de l'enregistrement portant un code.
     *
     * @param  string  $code  code (constante de la classe, ex. AttendanceStatus::PRESENT)
     */
    public static function idFor(string $code): ?int
    {
        return static::query()->where('code', $code)->value('id');
    }

    /**
     * Donne les identifiants des statuts portant des codes.
     *
     * @param  list<string>  $codes  codes des statuts (ex. AttendanceStatus::ATTENDING)
     * @return list<int>
     */
    public static function idsFor(array $codes): array
    {
        return array_values(static::query()->whereIn('code', $codes)->get()->map(fn (AttendanceStatus $status): int => $status->id)->all());
    }
}
