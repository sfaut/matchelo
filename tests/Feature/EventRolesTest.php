<?php

use App\Models\AttendanceStatus;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User\Role;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed();

    $this->paul = createUser(['email' => 'paul@example.org', 'first_name' => 'Paul', 'last_name' => 'Roche']);
    $this->lea = createUser();
    $this->rita = createUser(['email' => 'rita@example.org', 'first_name' => 'Rita', 'last_name' => 'Arnaud']);
    $this->tom = createUser(['email' => 'tom@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [
        Role::PRESIDENT => $this->paul,
        Role::ANIMATOR => $this->lea,
        Role::REFEREE => $this->rita,
        Role::PLAYER => $this->tom,
    ]);
});

/**
 * Donne le code du rôle tenu par chaque participant d'un événement.
 *
 * @return array<int, string> code du rôle, indexé par identifiant d'utilisateur
 */
function eventRoleCodes(int $event_id): array
{
    return DB::table('attendances')
        ->join('roles', 'roles.id', '=', 'attendances.role_id')
        ->where('event_id', $event_id)
        ->pluck('roles.code', 'user_id')
        ->all();
}

test('the expected members hold the event role given by their club role', function () {
    $course = $this->club->createCourse('2026-09-24');

    expect(eventRoleCodes($course->id))->toEqual([
        $this->paul->id => Role::ANIMATOR,
        $this->lea->id => Role::ANIMATOR,
        $this->rita->id => Role::REFEREE,
        $this->tom->id => Role::PLAYER,
    ])
        ->and($course->animators->pluck('id')->all())->toEqualCanonicalizing([$this->paul->id, $this->lea->id])
        ->and($course->players->pluck('id')->all())->toBe([$this->tom->id]);
});

test('marking attendance keeps the event role of an enrolled member', function () {
    $course = $this->club->createCourse('2026-09-24');
    $course->enrol([$this->rita->id], Role::PLAYER);

    $course->setAttendance([$this->rita->id], AttendanceStatus::PRESENT);

    expect(eventRoleCodes($course->id)[$this->rita->id])->toBe(Role::PLAYER)
        ->and($course->attendees->pluck('id')->all())->toBe([$this->rita->id]);
});

test('a newcomer gets the event role of their club role, a non-member plays', function () {
    $course = $this->club->createCourse('2026-09-24');
    $newcomer = createUser(['email' => 'nina@example.org', 'first_name' => 'Nina', 'last_name' => 'Morel']);
    $this->club->members()->attach($newcomer, ['role_id' => Role::idFor(Role::ANIMATOR)]);
    $guest = createUser(['email' => 'guest@example.org', 'first_name' => 'Hugo', 'last_name' => 'Petit']);

    $course->setAttendance([$newcomer->id, $guest->id], AttendanceStatus::PRESENT);

    expect(eventRoleCodes($course->id))->toMatchArray([$newcomer->id => Role::ANIMATOR, $guest->id => Role::PLAYER])
        ->and($course->attendees->pluck('id')->all())->toEqualCanonicalizing([$newcomer->id, $guest->id]);
});

test('scheduling a course enrols its animators and players as expected', function () {
    $course = $this->club->scheduleCourse('2026-10-15 17:30:00', [$this->rita->id], [$this->tom->id, $this->lea->id]);

    expect($course->type->code)->toBe(EventType::COURSE)
        ->and($course->status->code)->toBe(EventStatus::SCHEDULED)
        ->and($course->scheduled_at->format('Y-m-d H:i:s'))->toBe('2026-10-15 17:30:00')
        ->and(eventRoleCodes($course->id))->toEqual([
            $this->rita->id => Role::ANIMATOR,
            $this->tom->id => Role::PLAYER,
            $this->lea->id => Role::PLAYER,
        ])
        ->and($course->participants()->wherePivot('attendance_status_id', AttendanceStatus::idFor(AttendanceStatus::EXPECTED))->count())->toBe(3);
});

test('a course needs at least one animator and one player', function (Closure $participant_ids) {
    expect(fn () => $this->club->scheduleCourse('2026-10-15 17:30:00', ...$participant_ids->call($this)))
        ->toThrow(InvalidArgumentException::class);

    expect($this->club->events()->count())->toBe(0);
})->with([
    'no animator' => [fn (): array => [[], [$this->tom->id]]],
    'no player' => [fn (): array => [[$this->lea->id], []]],
]);

test('the candidates for an event role list the members of the matching club roles first', function (string $event_role_code, Closure $expected) {
    expect($this->club->candidatesFor($event_role_code)->pluck('users.id')->all())->toBe($expected->call($this));
})->with([
    'animators: president and animator first' => [Role::ANIMATOR, fn (): array => [$this->lea->id, $this->paul->id, $this->rita->id, $this->tom->id]],
    'players: player first' => [Role::PLAYER, fn (): array => [$this->tom->id, $this->rita->id, $this->lea->id, $this->paul->id]],
    'referees: referee first' => [Role::REFEREE, fn (): array => [$this->rita->id, $this->tom->id, $this->lea->id, $this->paul->id]],
]);

test('each club role leads to an event role, the president animating', function () {
    expect(Role::clubRolesFor(Role::ANIMATOR))->toBe([Role::PRESIDENT, Role::ANIMATOR])
        ->and(Role::clubRolesFor(Role::PRESIDENT))->toBe([])
        ->and(Role::eventRoleIds())->toEqual([
            Role::idFor(Role::PRESIDENT) => Role::idFor(Role::ANIMATOR),
            Role::idFor(Role::ANIMATOR) => Role::idFor(Role::ANIMATOR),
            Role::idFor(Role::REFEREE) => Role::idFor(Role::REFEREE),
            Role::idFor(Role::PLAYER) => Role::idFor(Role::PLAYER),
        ]);
});
