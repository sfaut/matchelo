<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Clubs de test : nom d'établissement, code d'invitation unique, créateur inscrit comme animateur.
 * Les rôles (RoleSeeder) doivent exister.
 *
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    /**
     * Descriptions de séance proposées.
     *
     * @var list<string>
     */
    public const array DESCRIPTIONS = [
        'Lundi 12h30 · CDI',
        'Mardi 17h · salle 12',
        'Mercredi 14h · foyer',
        'Jeudi 12h30 · CDI',
        'Vendredi 16h30 · salle polyvalente',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Échecs', 'Club d’échecs', 'Échiquier']).' · '
                .fake()->randomElement(['Collège', 'Lycée', 'École']).' '.fake()->lastName(),
            'code' => fn (array $attributes): string => Club::generateCode($attributes['name']),
            'city' => fake()->city(),
            'description' => fake()->randomElement(self::DESCRIPTIONS),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Club sans ville ni description.
     */
    public function bare(): static
    {
        return $this->state(fn (): array => [
            'city' => '',
            'description' => '',
        ]);
    }

    /**
     * Configure the model factory : le créateur devient animateur du club.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Club $club): void {
            $club->members()->syncWithoutDetaching([
                $club->user_id => ['role_id' => Role::idFor(Role::ANIMATOR)],
            ]);
        });
    }

    /**
     * Ajoute au club des élèves joueurs, avec ou sans compte.
     *
     * @param  int  $count  nombre d'élèves
     * @param  float  $without_account_share  part des élèves sans compte (0 à 1)
     */
    public function withStudents(int $count, float $without_account_share = 0.5): static
    {
        return $this->afterCreating(function (Club $club) use ($count, $without_account_share): void {
            $without_account = (int) round($count * $without_account_share);
            $students = User::factory()->withoutAccount()->count($without_account)->create()
                ->concat(User::factory()->count($count - $without_account)->create());

            $club->members()->attach($students->pluck('id'), ['role_id' => Role::idFor(Role::PLAYER)]);
        });
    }
}
