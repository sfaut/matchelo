<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ClubSeeder::class,
        ]);

        // Données de démonstration en local seulement : les tests (environnement testing)
        // gardent une base minimale
        if (app()->isLocal()) {
            $this->call(DemoSeeder::class);
        }
    }
}
