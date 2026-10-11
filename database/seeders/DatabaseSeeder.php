<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Alimente la base. Les événements de modèle restent actifs : ils historisent les statuts
 * et les rôles (RecordsChanges) et complètent les adhésions.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ClubSeeder::class,
            EventSeeder::class,
        ]);

        // Données de démonstration en local seulement : les tests (environnement testing)
        // gardent une base minimale
        if (app()->isLocal()) {
            $this->call(DemoSeeder::class);
        }
    }
}
