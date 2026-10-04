<?php

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use Database\Factories\UserFactory;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed();
});

test('a factory user has a verified account with the known password', function () {
    $user = User::factory()->create();

    expect($user->hasAccount())->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->elo)->toBeBetween(900, 1600)
        ->and(Auth::attempt(['email' => $user->email, 'password' => UserFactory::PASSWORD]))->toBeTrue();
});

test('a factory player without account has neither email nor password hash', function () {
    $player = User::factory()->withoutAccount()->create()->refresh();

    expect($player->email)->toBeNull()
        ->and($player->email_verified_at)->toBeNull()
        ->and($player->password_hash)->toBe('')
        ->and($player->hasAccount())->toBeFalse();
});

test('factory states make unverified accounts and administrators', function () {
    expect(User::factory()->unverified()->create()->email_verified_at)->toBeNull()
        ->and(User::factory()->administrator()->create()->refresh()->is_administrator)->toBeTrue();
});

test('a factory club has an invite code and its creator as animator', function () {
    $club = Club::factory()->create();

    expect($club->code)->toMatch('/^[A-Z]{2}-\d{4}$/')
        ->and($club->city)->not->toBe('')
        ->and($club->members)->toHaveCount(1)
        ->and($club->members->first()->is($club->creator))->toBeTrue()
        ->and($club->members->first()->pivot->role_id)->toBe(Role::idFor(Role::ANIMATOR));
});

test('a bare factory club has neither city nor description', function () {
    $club = Club::factory()->bare()->create();

    expect($club->city)->toBe('')->and($club->description)->toBe('');
});

test('a factory club can be filled with students, half of them without account', function () {
    $club = Club::factory()->withStudents(10)->create();
    $students = $club->members()->wherePivot('role_id', Role::idFor(Role::PLAYER))->get();

    expect($students)->toHaveCount(10)
        ->and($students->filter(fn (User $student): bool => ! $student->hasAccount()))->toHaveCount(5);
});

test('the demo seeder makes 3 animators with 3 to 5 clubs of 8 to 12 students', function () {
    $this->seed(DemoSeeder::class);

    $animators = User::where('email', 'like', 'animateur%@matchelo.test')->get();

    expect($animators)->toHaveCount(DemoSeeder::ANIMATORS);

    foreach ($animators as $animator) {
        $clubs = $animator->createdClubs;

        expect($clubs->count())->toBeBetween(...DemoSeeder::CLUBS_PER_ANIMATOR)
            ->and($animator->isAnimator())->toBeTrue();

        foreach ($clubs as $club) {
            expect($club->members()->wherePivot('role_id', Role::idFor(Role::PLAYER))->count())
                ->toBeBetween(...DemoSeeder::STUDENTS_PER_CLUB);
        }
    }
});
