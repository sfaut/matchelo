<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Séances de test : une date des deux derniers mois, pour un club.
 *
 * @extends Factory<ClubSession>
 */
class ClubSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'scheduled_at' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
        ];
    }

    /**
     * Pointe des membres présents à la séance.
     *
     * @param  iterable<User>  $attendees  membres présents
     */
    public function attendedBy(iterable $attendees): static
    {
        return $this->afterCreating(function (ClubSession $session) use ($attendees): void {
            $session->attendees()->attach(collect($attendees)->pluck('id'));
        });
    }
}
