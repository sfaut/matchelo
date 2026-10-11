<?php

namespace Database\Seeders;

use App\Models\MembershipStatus;
use Illuminate\Database\Seeder;

/**
 * Tables de référence des clubs : statuts d'adhésion.
 */
class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'code' => 'PENDING',   'name' => 'En attente', 'description' => "Demande d'adhésion à valider par un gestionnaire", 'display_order' => 10],
            ['id' => 2, 'code' => 'ACTIVE',    'name' => 'Actif',      'description' => 'Membre du club à part entière', 'display_order' => 20],
            ['id' => 3, 'code' => 'SUSPENDED', 'name' => 'Suspendu',   'description' => 'Membre temporairement écarté, sans droit au club', 'display_order' => 30],
            ['id' => 4, 'code' => 'LEFT',      'name' => 'Parti',      'description' => 'Ancien membre, adhésion et historique conservés', 'display_order' => 40],
        ];

        foreach ($statuses as $record) {
            MembershipStatus::create($record);
        }
    }
}
