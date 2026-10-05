<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use App\Models\User\Role;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Clubs de test : nom d'établissement, code d'invitation unique, créateur inscrit comme animateur.
 * Les rôles (RoleSeeder) doivent exister.
 *
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    /**
     * Descriptions de séance proposées, indexées par jour de la semaine (ISO : 1 = lundi).
     *
     * @var array<int, string>
     */
    public const array DESCRIPTIONS = [
        CarbonImmutable::MONDAY => 'Lundi 12h30 · CDI',
        CarbonImmutable::TUESDAY => 'Mardi 17h · salle 12',
        CarbonImmutable::WEDNESDAY => 'Mercredi 14h · foyer',
        CarbonImmutable::THURSDAY => 'Jeudi 12h30 · CDI',
        CarbonImmutable::FRIDAY => 'Vendredi 16h30 · salle polyvalente',
    ];

    /**
     * Part des élèves peu assidus (présents de 10 à 35 % des fois).
     *
     * @var float
     */
    public const float IRREGULAR_SHARE = 0.15;

    /**
     * Part des présents de la séance du jour déjà pointés (appel en cours).
     *
     * @var float
     */
    public const float TODAY_CHECKED_SHARE = 0.6;

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

    /**
     * Ajoute une séance hebdomadaire sur les dernières semaines, le jour de la description (au
     * hasard sinon), la plus récente au plus tard aujourd'hui. À appliquer après withStudents() :
     * chaque membre a sa propre assiduité, et quelques-uns viennent rarement.
     *
     * @param  int  $weeks  nombre de séances
     */
    public function withSessions(int $weeks = 8): static
    {
        return $this->afterCreating(function (Club $club) use ($weeks): void {
            $weekday = $this->weekdayOf($club) ?? fake()->numberBetween(CarbonImmutable::MONDAY, CarbonImmutable::FRIDAY);
            $last_date = CarbonImmutable::today();

            while ($last_date->dayOfWeekIso !== $weekday) {
                $last_date = $last_date->subDay();
            }

            $assiduity = $this->assiduityOf($club->members()->get());

            for ($week = $weeks - 1; $week >= 0; $week--) {
                $date = $last_date->subWeeks($week);
                $checked_share = $date->isToday() ? self::TODAY_CHECKED_SHARE : 1.0;

                ClubSession::factory()
                    ->for($club)
                    ->attendedBy($this->drawAttendees($assiduity, $checked_share))
                    ->create(['scheduled_at' => $date->toDateString()]);
            }
        });
    }

    /**
     * Ajoute une séance aujourd'hui, appel en cours, si le club n'en a pas déjà une.
     */
    public function withTodaySession(): static
    {
        return $this->afterCreating(function (Club $club): void {
            if ($club->sessions()->whereDate('scheduled_at', CarbonImmutable::today())->exists()) {
                return;
            }

            ClubSession::factory()
                ->for($club)
                ->attendedBy($this->drawAttendees($this->assiduityOf($club->members()->get()), self::TODAY_CHECKED_SHARE))
                ->create(['scheduled_at' => CarbonImmutable::today()->toDateString()]);
        });
    }

    /**
     * Jour de séance indiqué en tête de la description (« Jeudi 12h30 · CDI »), ou null.
     */
    protected function weekdayOf(Club $club): ?int
    {
        $weekday = array_search(Str::before($club->description, ' '), array_map(
            fn (string $description): string => Str::before($description, ' '),
            self::DESCRIPTIONS,
        ), true);

        return $weekday === false ? null : $weekday;
    }

    /**
     * Probabilité de présence de chaque membre : la plupart sont assidus, quelques-uns non.
     *
     * @param  Collection<int, User>  $members  membres du club
     * @return Collection<int, array{user: User, probability: float}>
     */
    protected function assiduityOf(Collection $members): Collection
    {
        return $members->map(fn (User $member): array => [
            'user' => $member,
            'probability' => fake()->boolean((int) (100 * self::IRREGULAR_SHARE))
                ? fake()->randomFloat(2, 0.1, 0.35)
                : fake()->randomFloat(2, 0.7, 0.97),
        ]);
    }

    /**
     * Tire les présents d'une séance.
     *
     * @param  Collection<int, array{user: User, probability: float}>  $assiduity  assiduité des membres
     * @param  float  $checked_share  part des présents déjà pointés (1 pour une séance passée)
     * @return Collection<int, User>
     */
    protected function drawAttendees(Collection $assiduity, float $checked_share): Collection
    {
        return $assiduity
            ->filter(fn (array $row): bool => fake()->randomFloat(4, 0, 1) < $row['probability'] * $checked_share)
            ->pluck('user')
            ->values();
    }
}
