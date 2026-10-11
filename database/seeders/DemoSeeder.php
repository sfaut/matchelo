<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Database\Factories\ClubFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Données de démonstration : 3 animateurs (animateur1@matchelo.test… mot de passe « password »),
 * chacun avec 3 à 5 clubs de 8 à 12 élèves, la moitié environ sans compte. Chaque club a une
 * séance par semaine depuis 8 semaines ; le premier club de chaque animateur a en plus une
 * séance aujourd'hui, appel en cours.
 */
class DemoSeeder extends Seeder
{
    /**
     * Nombre d'animateurs.
     *
     * @var int
     */
    public const int ANIMATORS = 3;

    /**
     * Nombre de clubs par animateur : minimum et maximum.
     *
     * @var array{int, int}
     */
    public const array CLUBS_PER_ANIMATOR = [3, 5];

    /**
     * Nombre d'élèves par club : minimum et maximum.
     *
     * @var array{int, int}
     */
    public const array STUDENTS_PER_CLUB = [8, 12];

    /**
     * Nombre de séances hebdomadaires passées par club.
     *
     * @var int
     */
    public const int WEEKS_OF_SESSIONS = 8;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Une seule transaction : bien plus rapide qu'une validation par insertion
        DB::transaction(function (): void {
            for ($number = 1; $number <= self::ANIMATORS; $number++) {
                $animator = User::factory()->create(['email' => "animateur{$number}@matchelo.test"]);

                $clubs_count = fake()->numberBetween(...self::CLUBS_PER_ANIMATOR);

                for ($club_number = 1; $club_number <= $clubs_count; $club_number++) {
                    Club::factory()
                        ->for($animator, 'creator')
                        ->withStudents(fake()->numberBetween(...self::STUDENTS_PER_CLUB))
                        ->withSessions(self::WEEKS_OF_SESSIONS)
                        ->when($club_number === 1, fn (ClubFactory $factory): ClubFactory => $factory->withTodaySession())
                        ->create();
                }
            }
        });
    }
}
