<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Historise des colonnes d'un modèle (statut, rôle) : sa création puis chaque changement d'une
 * colonne suivie ajoutent une ligne à la table d'historique de cette colonne, avec la valeur
 * prise, l'horodatage du changement (colonne métier changed_at) et son auteur.
 *
 * Une colonne suivie porte le même nom dans la table du modèle et dans son historique (ex.
 * memberships.role_id et membership_role_changes.role_id). L'auteur est l'utilisateur connecté,
 * à défaut (seeders, console) le créateur du club.
 *
 * @see docs/clubs.md
 * @see docs/events.md
 *
 * @mixin Model
 */
trait RecordsChanges
{
    /**
     * Horodatage à inscrire au prochain changement (changeRecorded()), à défaut l'instant présent.
     */
    protected CarbonInterface|string|null $recordedChangeAt = null;

    /**
     * Inscrit la valeur initiale de chaque colonne suivie à la création, puis chacun de ses
     * changements.
     */
    public static function bootRecordsChanges(): void
    {
        static::created(function (self $model): void {
            foreach ($model->recordedColumns() as $column) {
                $model->recordChange($column);
            }
        });

        static::updated(function (self $model): void {
            foreach ($model->recordedColumns() as $column) {
                if ($model->wasChanged($column)) {
                    $model->recordChange($column);
                }
            }
        });
    }

    /**
     * Change une colonne suivie et l'historise ; sans effet si la valeur ne change pas.
     *
     * @param  string  $column  colonne suivie (recordedColumns())
     * @param  int|null  $value  nouvelle valeur
     * @param  CarbonInterface|string|null  $changed_at  horodatage du changement, à défaut l'instant présent (un horodatage antérieur reprend un historique existant)
     */
    protected function changeRecorded(string $column, ?int $value, CarbonInterface|string|null $changed_at = null): void
    {
        $this->recordedChangeAt = $changed_at;

        try {
            $this->update([$column => $value]);
        } finally {
            $this->recordedChangeAt = null;
        }
    }

    /**
     * Ajoute à l'historique d'une colonne sa valeur courante, horodatée et attribuée à son auteur.
     *
     * @param  string  $column  colonne suivie (recordedColumns())
     */
    protected function recordChange(string $column): void
    {
        $this->historyOf($column)->create([
            $column => $this->getAttribute($column),
            'changed_at' => $this->recordedChangeAt ?? $this->freshTimestamp(),
            'user_id' => Auth::id() ?? $this->fallbackChangeAuthorId(),
        ]);
    }

    /**
     * Colonnes suivies.
     *
     * @return list<string>
     */
    abstract protected function recordedColumns(): array;

    /**
     * Historique d'une colonne suivie, du plus ancien au plus récent changement.
     *
     * @param  string  $column  colonne suivie (recordedColumns())
     * @return HasMany<covariant Model, $this>
     */
    abstract protected function historyOf(string $column): HasMany;

    /**
     * Auteur d'un changement fait sans utilisateur connecté : le créateur du club.
     */
    abstract protected function fallbackChangeAuthorId(): int;
}
