<?php

namespace App\Models\Concerns;

/**
 * Écrit les dates du modèle avec leurs microsecondes, pour les colonnes `dateTime(precision: 6)`.
 *
 * Le format par défaut d'Eloquent (celui du grammar, `Y-m-d H:i:s`) tronque les microsecondes
 * avant l'écriture en base.
 */
trait HasMicrosecondDates
{
    /**
     * Donne le format de stockage des dates du modèle.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.u';
    }
}
