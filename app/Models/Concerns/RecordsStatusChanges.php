<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Historise le statut d'un modèle : sa création puis chaque changement de sa colonne de statut
 * ajoutent une ligne à sa table d'historique, avec le statut pris, sa date et son auteur.
 *
 * La colonne de statut porte le même nom dans la table du modèle et dans son historique (ex.
 * memberships.membership_status_id et membership_status_changes.membership_status_id).
 * L'auteur est l'utilisateur connecté, à défaut (seeders, console) le créateur du club.
 *
 * @see docs/clubs.md
 * @see docs/events.md
 *
 * @template TStatusChange of Model
 *
 * @mixin Model
 */
trait RecordsStatusChanges
{
    /**
     * Date à inscrire au prochain changement de statut (changeStatus()), à défaut l'instant présent.
     *
     * @var CarbonInterface|string|null
     */
    protected CarbonInterface|string|null $statusChangedAt = null;

    /**
     * Inscrit le statut initial à la création, puis chaque changement de statut.
     */
    public static function bootRecordsStatusChanges(): void
    {
        static::created(function (self $model): void {
            $model->recordStatusChange();
        });

        static::updated(function (self $model): void {
            if ($model->wasChanged($model->statusColumn())) {
                $model->recordStatusChange();
            }
        });
    }

    /**
     * Change le statut et l'historise ; sans effet si le statut ne change pas.
     *
     * @param  string  $status_code  code du nouveau statut
     * @param  CarbonInterface|string|null  $changed_at  date du changement, à défaut l'instant présent (une date antérieure reprend un historique existant)
     */
    public function changeStatus(string $status_code, CarbonInterface|string|null $changed_at = null): void
    {
        $this->statusChangedAt = $changed_at;

        try {
            $this->update([$this->statusColumn() => $this->statusIdFor($status_code)]);
        } finally {
            $this->statusChangedAt = null;
        }
    }

    /**
     * Ajoute à l'historique le statut courant, daté et attribué à son auteur.
     */
    protected function recordStatusChange(): void
    {
        $this->statusChanges()->create([
            $this->statusColumn() => $this->getAttribute($this->statusColumn()),
            'changed_at' => $this->statusChangedAt ?? $this->freshTimestamp(),
            'user_id' => Auth::id() ?? $this->fallbackStatusAuthorId(),
        ]);
    }

    /**
     * Historique des statuts, du plus ancien au plus récent.
     *
     * @return HasMany<TStatusChange, $this>
     */
    abstract public function statusChanges(): HasMany;

    /**
     * Nom de la colonne de statut, commun au modèle et à son historique.
     */
    abstract protected function statusColumn(): string;

    /**
     * Donne l'identifiant d'un statut d'après son code.
     *
     * @param  string  $status_code  code du statut
     */
    abstract protected function statusIdFor(string $status_code): ?int;

    /**
     * Auteur d'un changement fait sans utilisateur connecté : le créateur du club.
     */
    abstract protected function fallbackStatusAuthorId(): int;
}
