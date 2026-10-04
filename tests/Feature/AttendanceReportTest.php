<?php

use App\Models\ClubSession;
use App\Models\User\Role;
use App\Support\AttendanceReport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed();
    Carbon::setTestNow('2026-10-01 10:00');

    $this->lea = createUser();
    $this->tom = createUser(['email' => 'tom@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->ines = createUser(['email' => 'ines@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci']);
    $this->hugo = createUser(['email' => 'hugo@example.org', 'first_name' => 'Hugo', 'last_name' => 'Petit']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [
        Role::ANIMATOR => $this->lea,
        Role::PLAYER => $this->tom,
    ]);
    $this->club->members()->attach([$this->ines->id, $this->hugo->id], ['role_id' => Role::idFor(Role::PLAYER)]);
});

test('a session belongs to its club and lists its attendees', function () {
    $session = createSession($this->club, '2026-09-24', [$this->lea, $this->tom]);

    expect($session->club->is($this->club))->toBeTrue()
        ->and($session->held_on->toDateString())->toBe('2026-09-24')
        ->and($session->attendees->pluck('id')->all())->toEqualCanonicalizing([$this->lea->id, $this->tom->id])
        ->and($this->club->sessions->pluck('id')->all())->toBe([$session->id]);
});

test('a club has a single session per date', function () {
    createSession($this->club, '2026-09-24');

    expect(fn () => createSession($this->club, '2026-09-24'))->toThrow(QueryException::class);
});

test('deleting a session deletes its attendances', function () {
    $session = createSession($this->club, '2026-09-24', [$this->lea]);
    $session->delete();

    expect(DB::table('attendances')->count())->toBe(0);
});

test('the report finds the session of the day with its attendees count', function () {
    expect(AttendanceReport::for($this->club)->todaySession())->toBeNull();

    createSession($this->club, '2026-10-01', [$this->lea, $this->tom, $this->ines]);

    expect(AttendanceReport::for($this->club)->todaySession()->attendees_count)->toBe(3);
});

test('the recent sessions run from oldest to newest, up to today', function () {
    foreach (['2026-09-03', '2026-09-10', '2026-09-17', '2026-09-24', '2026-10-01', '2026-10-08'] as $date) {
        createSession($this->club, $date, [$this->lea]);
    }

    $dates = AttendanceReport::for($this->club)->recentSessions(3)
        ->map(fn (ClubSession $session): string => $session->held_on->toDateString())
        ->all();

    expect($dates)->toBe(['2026-09-17', '2026-09-24', '2026-10-01']);
});

test('rates are the share of current members present, averaged over sessions', function () {
    createSession($this->club, '2026-09-24', [$this->lea, $this->tom, $this->ines]);
    createSession($this->club, '2026-10-01', [$this->lea]);
    $report = AttendanceReport::for($this->club);
    $sessions = $report->recentSessions(5);

    expect($report->rate($sessions[0], 4))->toBe(75)
        ->and($report->rate($sessions[1], 4))->toBe(25)
        ->and($report->averageRate($sessions, 4))->toBe(50)
        ->and($report->averageRate($sessions->take(0), 4))->toBeNull()
        ->and($report->rate($sessions[0], 0))->toBe(0);
});

test('members absent several times in a row are listed, the most absent first', function () {
    createSession($this->club, '2026-09-10', [$this->lea, $this->tom, $this->ines]);
    createSession($this->club, '2026-09-17', [$this->lea, $this->ines]);
    createSession($this->club, '2026-09-24', [$this->lea]);
    // La séance du jour ne compte pas : l'appel peut être en cours
    createSession($this->club, '2026-10-01', []);

    $rows = AttendanceReport::for($this->club)->consecutiveAbsences()
        ->map(fn (array $row): array => [$row['user']->last_name, $row['absences']])
        ->all();

    expect($rows)->toBe([['Petit', 3], ['Bernard', 2]]);
});

test('past presences count the sessions before today for each member', function () {
    createSession($this->club, '2026-09-17', [$this->lea, $this->tom]);
    createSession($this->club, '2026-09-24', [$this->lea]);
    createSession($this->club, '2026-10-01', [$this->tom]);

    $presences = AttendanceReport::for($this->club)->pastPresences();

    expect($presences['sessions'])->toBe(2)
        ->and($presences['presences'][$this->lea->id])->toBe(2)
        ->and($presences['presences'][$this->tom->id])->toBe(1)
        ->and($presences['presences'])->not->toHaveKey($this->hugo->id);
});
