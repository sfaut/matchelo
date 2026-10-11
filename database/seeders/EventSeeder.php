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
            ['id' => 1, 'code' => 'DRAFT',       'name' => 'Brouillon', 'description' => 'Événement en cours de création, non visible'],
            ['id' => 2, 'code' => 'SCHEDULED',   'name' => 'Planifié',  'description' => 'Événement confirmé et planifié'],
            ['id' => 3, 'code' => 'IN_PROGRESS', 'name' => 'En cours',  'description' => 'Événement en cours de réalisation'],
            ['id' => 4, 'code' => 'COMPLETED',   'name' => 'Terminé',   'description' => 'Événement terminé, pointage encore modifiable'],
            ['id' => 5, 'code' => 'FINALIZED',   'name' => 'Clôturé',   'description' => 'Événement terminé et pointage définitivement clos'],
            ['id' => 6, 'code' => 'CANCELLED',   'name' => 'Annulé',    'description' => 'Événement annulé'],
        ];

        foreach ($statuses as $record) {
            EventStatus::create($record);
        }

        $attendance_statuses = [
            ['id' => 1, 'code' => 'PRESENT', 'name' => 'Présent', 'description' => "Membre présent à l'événement"],
            ['id' => 2, 'code' => 'ABSENT',  'name' => 'Absent',  'description' => "Membre absent de l'événement"],
            ['id' => 3, 'code' => 'EXCUSED', 'name' => 'Excusé',  'description' => "Membre absent de l'événement, excusé"],
        ];

        foreach ($attendance_statuses as $record) {
            AttendanceStatus::create($record);
        }
    }
}
