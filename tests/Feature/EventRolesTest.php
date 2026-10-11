<?php

use App\Models\AttendanceStatus;
use App\Models\User\Role;

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

test('enrolling an enrolled member changes their role and keeps their attendance', function () {
    $course = $this->club->createCourse('2026-09-24');
    $course->setAttendance([$this->tom->id], AttendanceStatus::PRESENT);

    $course->enrol([$this->tom->id], Role::ANIMATOR);

    expect(eventRoleCodes($course->id)[$this->tom->id])->toBe(Role::ANIMATOR)
        ->and($course->attendees->pluck('id')->all())->toBe([$this->tom->id]);
});

test('a course is staffed with at least one animator and one player', function () {
    $course = $this->club->createCourse('2026-09-24');

    expect($course->isStaffed())->toBeTrue();

    $course->withdraw([$this->tom->id]);

    expect($course->isStaffed())->toBeFalse()
        ->and($course->participants()->whereKey($this->tom->id)->exists())->toBeFalse();
});

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
