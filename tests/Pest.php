<?php

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Crée un utilisateur de test (tables de référence à alimenter avant).
 *
 * @param  array<string, mixed>  $attributes  attributs à surcharger
 */
function createUser(array $attributes = []): User
{
    return User::create([
        'last_name' => 'Martin',
        'first_name' => 'Léa',
        'email' => 'lea.martin@example.org',
        'password_hash' => 'secret-password',
        ...$attributes,
    ]);
}

/**
 * Crée un club et y inscrit des membres avec leur rôle.
 *
 * @param  string  $name  nom du club
 * @param  array<string, User>  $members  membres indexés par code de rôle (Role::ANIMATOR, Role::PLAYER)
 */
function createClubWith(string $name, array $members): Club
{
    $club = Club::create(['name' => $name, 'code' => Club::generateCode($name), 'user_id' => reset($members)->id]);

    foreach ($members as $role_code => $member) {
        $club->members()->attach($member, ['role_id' => Role::idFor($role_code)]);
    }

    return $club;
}

/**
 * Crée une séance d'un club à une date, avec ses présents.
 *
 * @param  string  $date  date de la séance (AAAA-MM-JJ)
 * @param  list<User>  $attendees  membres présents
 */
function createSession(Club $club, string $date, array $attendees = []): ClubSession
{
    $session = $club->sessions()->create(['scheduled_at' => $date]);
    $session->attendees()->attach(collect($attendees)->pluck('id'));

    return $session;
}
