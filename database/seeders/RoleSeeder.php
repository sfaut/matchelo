<?php

namespace Database\Seeders;

use App\Models\User\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'PRESIDENT', 'name' => 'Président', 'description' => "Dirige le club : droits de l'animateur, plus la gestion des animateurs et du club."],
            ['id' => 2, 'code' => 'ANIMATOR', 'name' => 'Animateur', 'description' => 'Anime le club : séances, présences, joueurs, tournois et validation des résultats.'],
            ['id' => 3, 'code' => 'PLAYER', 'name' => 'Joueur', 'description' => "Joue au club : consulte le classement et ses statistiques, s'inscrit aux tournois, saisit ses résultats."],
        ];

        foreach ($data as $record) {
            Role::create($record);
        }
    }
}
