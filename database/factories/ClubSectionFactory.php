<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubSection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Sections de test : un nom courant de section, pour un club.
 *
 * @extends Factory<ClubSection>
 */
class ClubSectionFactory extends Factory
{
    /**
     * Noms de section proposés, en plus de la section générale.
     *
     * @var list<string>
     */
    public const array NAMES = ['Section jeunes', 'Section vétérans', 'Section compétition', 'Section mardi', 'Section mercredi'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->randomElement(self::NAMES),
        ];
    }

    /**
     * Inscrit des joueurs dans la section.
     *
     * @param  iterable<User>  $players  joueurs de la section
     */
    public function withPlayers(iterable $players): static
    {
        return $this->afterCreating(function (ClubSection $section) use ($players): void {
            $section->players()->attach(collect($players)->pluck('id'));
        });
    }
}
