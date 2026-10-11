<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use App\Models\EventStatus;
use App\Models\EventType;
use Illuminate\Database\Seeder;

/**
 * Tables de référence des événements : types, statuts d'événement et statuts de présence.
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'code' => 'COURSE',     'name' => 'Cours',   'description' => 'Séance habituelle du club'],
            ['id' => 2, 'code' => 'TOURNAMENT', 'name' => 'Tournoi', 'description' => 'Tournoi entre joueurs du club'],
            ['id' => 3, 'code' => 'OTHER',      'name' => 'Autre',   'description' => 'Autre événement du club'],
        ];

        foreach ($types as $record) {
            EventType::create($record);
        }

        $statuses = [
            ['id' => 1, 'code' => 'DRAFT',       'name' => 'Brouillon', 'description' => 'Événement en cours de création, non visible', 'display_order' => 10],
            ['id' => 2, 'code' => 'SCHEDULED',   'name' => 'Planifié',  'description' => 'Événement confirmé et planifié', 'display_order' => 20],
            ['id' => 3, 'code' => 'IN_PROGRESS', 'name' => 'En cours',  'description' => 'Événement en cours de réalisation', 'display_order' => 30],
            ['id' => 4, 'code' => 'COMPLETED',   'name' => 'Terminé',   'description' => 'Événement terminé, pointage encore modifiable', 'display_order' => 40],
            ['id' => 5, 'code' => 'FINALIZED',   'name' => 'Clôturé',   'description' => 'Événement terminé et pointage définitivement clos', 'display_order' => 50],
            ['id' => 6, 'code' => 'CANCELLED',   'name' => 'Annulé',    'description' => 'Événement annulé', 'display_order' => 60],
        ];

        foreach ($statuses as $record) {
            EventStatus::create($record);
        }

        $attendance_statuses = [
            ['id' => 1, 'code' => 'EXPECTED', 'name' => 'Prévu',     'description' => 'Joueur attendu', 'display_order' => 10],
            ['id' => 2, 'code' => 'PRESENT',  'name' => 'Présent',   'description' => 'Joueur présent', 'display_order' => 20],
            ['id' => 3, 'code' => 'ABSENT',   'name' => 'Absent',    'description' => 'Joueur absent non excusé', 'display_order' => 30],
            ['id' => 4, 'code' => 'EXCUSED',  'name' => 'Excusé',    'description' => 'Joueur absent avec excuse acceptée', 'display_order' => 40],
            ['id' => 5, 'code' => 'LATE',     'name' => 'En retard', 'description' => 'Joueur arrivé en retard', 'display_order' => 50],
        ];

        foreach ($attendance_statuses as $record) {
            AttendanceStatus::create($record);
        }
    }
}
