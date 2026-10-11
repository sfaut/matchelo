<?php

namespace Database\Factories;

use App\Models\AttendanceStatus;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Événements de test : un cours terminé, à une date des deux derniers mois, pour un club, chaque
 * membre attendu.
 * Les tables de référence (EventSeeder) doivent exister.
 *
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scheduled_at' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'event_type_id' => fn (): ?int => EventType::idFor(EventType::COURSE),
            'event_status_id' => fn (): ?int => EventStatus::idFor(EventStatus::COMPLETED),
            'club_id' => Club::factory(),
        ];
    }

    /**
     * Configure the model factory : chaque membre du club est attendu à l'événement.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Event $event): void {
            $event->expectMembers();
        });
    }

    /**
     * Pointe des membres présents à l'événement.
     *
     * @param  iterable<User>  $attendees  membres présents
     */
    public function attendedBy(iterable $attendees): static
    {
        return $this->afterCreating(function (Event $event) use ($attendees): void {
            $event->setAttendance(collect($attendees)->pluck('id'), AttendanceStatus::PRESENT);
        });
    }
}
