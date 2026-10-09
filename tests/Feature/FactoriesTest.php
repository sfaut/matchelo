<?php

use App\Models\Club;
use App\Models\ClubSession;
use App\Models\User;
use App\Models\User\Role;
use Carbon\CarbonImmutable;
use Database\Factories\ClubFactory;
use Database\Factories\UserFactory;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed();
});

test('a factory user has a verified account with the known password', function () {
    $user = User::factory()->create();

    expect($user->hasAccount())->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->elo_rating)->toBeBetween(900, 1600)
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

test('a factory session belongs to a club and can be attended', function () {
    $club = Club::factory()->withStudents(4)->create();
    $session = ClubSession::factory()->for($club)->attendedBy($club->members->take(2))->create();

    expect($session->club->is($club))->toBeTrue()
        ->and($session->scheduled_at->lessThanOrEqualTo(today()))->toBeTrue()
        ->and($session->attendees)->toHaveCount(2);
});

test('weekly factory sessions fall on the club day, the latest at most today', function () {
    Carbon::setTestNow('2026-10-01 10:00');

    $club = Club::factory()
        ->state(['description' => ClubFactory::DESCRIPTIONS[CarbonImmutable::TUESDAY]])
        ->withStudents(6)
        ->withSessions(4)
        ->create();
    $dates = $club->sessions()->orderBy('scheduled_at')->pluck('scheduled_at')
        ->map(fn (CarbonImmutable $date): string => $date->toDateString())
        ->all();
    $member_ids = $club->members()->pluck('users.id')->all();

    expect($dates)->toBe(['2026-09-08', '2026-09-15', '2026-09-22', '2026-09-29']);

    foreach ($club->sessions as $session) {
        expect(array_diff($session->attendees->pluck('id')->all(), $member_ids))->toBe([]);
    }
});

test('a club without day in its description still gets weekly sessions', function () {
    $club = Club::factory()->bare()->withSessions(3)->create();

    expect($club->sessions()->count())->toBe(3)
        ->and($club->sessions->pluck('scheduled_at')->map(fn (CarbonImmutable $date): int => $date->dayOfWeekIso)->unique())->toHaveCount(1);
});

test('the factory can add a session today, only once', function () {
    Carbon::setTestNow('2026-10-01 10:00');

    $club = Club::factory()
        ->state(['description' => ClubFactory::DESCRIPTIONS[CarbonImmutable::THURSDAY]])
        ->withStudents(10)
        ->withSessions(2)
        ->withTodaySession()
        ->create();

    expect($club->sessions()->whereDate('scheduled_at', '2026-10-01')->count())->toBe(1)
        ->and($club->sessions()->count())->toBe(2);

    $other_club = Club::factory()->withStudents(10)->withTodaySession()->create();

    expect($other_club->sessions()->whereDate('scheduled_at', '2026-10-01')->count())->toBe(1);
});

test('the demo clubs have weekly sessions, the first club of each animator one today', function () {
    Carbon::setTestNow('2026-10-01 10:00');

    $this->seed(DemoSeeder::class);

    foreach (User::where('email', 'like', 'animateur%@matchelo.test')->get() as $animator) {
        $clubs = $animator->createdClubs()->orderBy('id')->get();

        foreach ($clubs as $club) {
            expect($club->sessions()->whereDate('scheduled_at', '<', '2026-10-01')->count())
                ->toBeGreaterThanOrEqual(DemoSeeder::WEEKS_OF_SESSIONS - 1);
        }

        expect($clubs->first()->sessions()->whereDate('scheduled_at', '2026-10-01')->exists())->toBeTrue();
    }

    $club = Club::query()->orderBy('id')->first();

    $this->actingAs($club->creator)
        ->get(route('clubs.dashboard', $club))
        ->assertOk()
        ->assertSee(__('clubs.dashboard.in_progress'));
});
