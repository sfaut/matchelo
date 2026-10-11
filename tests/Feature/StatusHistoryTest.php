<?php

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceStatusChange;
use App\Models\EventStatus;
use App\Models\EventStatusChange;
use App\Models\EventType;
use App\Models\Membership;
use App\Models\MembershipStatus;
use App\Models\MembershipStatusChange;
use App\Models\User\Role;
use App\Support\EventPlanner;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed();
    $this->travelTo(Carbon::parse('2026-10-01 10:00:00'));

    $this->paul = createUser(['email' => 'paul@example.org', 'first_name' => 'Paul', 'last_name' => 'Roche']);
    $this->lea = createUser();
    $this->tom = createUser(['email' => 'tom@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [
        Role::PRESIDENT => $this->paul,
        Role::ANIMATOR => $this->lea,
        Role::PLAYER => $this->tom,
    ]);
    $this->tom_membership = $this->club->memberships()->where('user_id', $this->tom->id)->firstOrFail();
});

/**
 * Résume un historique de statuts : code du statut, date du changement et auteur.
 *
 * @param  iterable<MembershipStatusChange|EventStatusChange|AttendanceStatusChange>  $changes  changements de statut (relation statusChanges)
 * @param  string  $date_format  format de la date du changement
 * @return list<array{string, string, int}>
 */
function statusHistory(iterable $changes, string $date_format = 'Y-m-d'): array
{
    return collect($changes)->map(fn (MembershipStatusChange|EventStatusChange|AttendanceStatusChange $change): array => [$change->status->code, $change->changed_at->format($date_format), $change->user_id])->all();
}

test('a membership is active by default, its initial status recorded at its joining date by the club creator', function () {
    expect($this->tom_membership->status->code)->toBe(MembershipStatus::ACTIVE)
        ->and(statusHistory($this->tom_membership->statusChanges))->toBe([[MembershipStatus::ACTIVE, '2026-10-01', $this->paul->id]]);

    $former = Membership::create([
        'club_id' => $this->club->id,
        'user_id' => createUser(['email' => 'ines@example.org'])->id,
        'role_id' => Role::idFor(Role::PLAYER),
        'joined_at' => '2024-09-12',
    ]);

    expect(statusHistory($former->statusChanges))->toBe([[MembershipStatus::ACTIVE, '2024-09-12', $this->paul->id]]);
});

test('each membership status change is recorded with its date and the connected author, by date', function () {
    $this->actingAs($this->lea);

    $this->travelTo(Carbon::parse('2026-11-05 18:00:00'));
    $this->tom_membership->changeStatus(MembershipStatus::SUSPENDED);
    $this->tom_membership->changeStatus(MembershipStatus::SUSPENDED);
    $this->tom_membership->changeStatus(MembershipStatus::LEFT, changed_at: '2026-11-20');

    expect($this->tom_membership->fresh()->status->code)->toBe(MembershipStatus::LEFT)
        ->and(statusHistory($this->tom_membership->fresh()->statusChanges))->toBe([
            [MembershipStatus::ACTIVE, '2026-10-01', $this->paul->id],
            [MembershipStatus::SUSPENDED, '2026-11-05', $this->lea->id],
            [MembershipStatus::LEFT, '2026-11-20', $this->lea->id],
        ]);
});

test('only active memberships make members, and only they give rights', function (string $status_code) {
    $this->paul->memberships()->firstOrFail()->changeStatus($status_code);

    expect($this->club->members()->whereKey($this->paul->id)->exists())->toBeFalse()
        ->and($this->paul->clubs)->toBeEmpty()
        ->and($this->paul->presides($this->club))->toBeFalse()
        ->and($this->paul->can('manage', $this->club))->toBeFalse()
        ->and($this->paul->can('planEvents', $this->club))->toBeFalse()
        ->and($this->club->memberships()->count())->toBe(3);
})->with([MembershipStatus::PENDING, MembershipStatus::SUSPENDED, MembershipStatus::LEFT]);

test('admitting a user creates their membership, or reactivates it with its role', function () {
    $ines = createUser(['email' => 'ines@example.org']);

    $membership = $this->club->admit($ines, Role::PLAYER);

    expect($membership->status->code)->toBe(MembershipStatus::ACTIVE)
        ->and($membership->role->code)->toBe(Role::PLAYER);

    $this->tom_membership->changeStatus(MembershipStatus::LEFT);
    $this->club->admit($this->tom, Role::ANIMATOR);

    expect($this->tom_membership->fresh()->role->code)->toBe(Role::PLAYER)
        ->and($this->tom_membership->fresh()->statusChanges->map(fn (MembershipStatusChange $change): string => $change->status->code)->all())
        ->toBe([MembershipStatus::ACTIVE, MembershipStatus::LEFT, MembershipStatus::ACTIVE])
        ->and($this->club->members()->whereKey($this->tom->id)->exists())->toBeTrue();
});

test('an event records its initial status, then each change', function () {
    $planner = new EventPlanner($this->club);
    $event = $planner->generate(['2026-10-06 17:30'], EventType::COURSE, [$this->lea->id], [$this->tom->id])->first();

    $this->actingAs($this->paul);
    $this->travelTo(Carbon::parse('2026-10-02 08:15:00'));
    $planner->update([$event->id], status_code: EventStatus::CANCELLED);
    $planner->update([$event->id], description: 'Reporté');

    expect(statusHistory($event->fresh()->statusChanges, 'Y-m-d H:i'))->toBe([
        [EventStatus::SCHEDULED, '2026-10-01 10:00', $this->paul->id],
        [EventStatus::CANCELLED, '2026-10-02 08:15', $this->paul->id],
    ]);
});

test('an attendance records its initial status, then each change of the roll call', function () {
    $course = $this->club->createCourse('2026-10-01');
    $tom_attendance = $course->attendances()->where('user_id', $this->tom->id)->firstOrFail();

    $this->actingAs($this->lea);
    $this->travelTo(Carbon::parse('2026-10-01 17:35:00'));
    $course->setAttendance([$this->tom->id], AttendanceStatus::PRESENT);
    $course->setAttendance([$this->tom->id], AttendanceStatus::PRESENT);
    $this->travelTo(Carbon::parse('2026-10-01 17:40:00'));
    $course->resetAttendance();

    expect(statusHistory($tom_attendance->statusChanges, 'H:i'))->toBe([
        [AttendanceStatus::EXPECTED, '10:00', $this->paul->id],
        [AttendanceStatus::PRESENT, '17:35', $this->lea->id],
        [AttendanceStatus::EXPECTED, '17:40', $this->lea->id],
    ])
        ->and(DB::table('attendance_status_changes')->count())->toBe(5);
});

test('a participant is pointed through their attendance, with its own id', function () {
    $course = $this->club->createCourse('2026-10-01');
    $participant = $course->participants()->whereKey($this->tom->id)->firstOrFail();

    expect($participant->attendance)->toBeInstanceOf(Attendance::class)
        ->and($participant->attendance->id)->toBeInt()
        ->and(Attendance::find($participant->attendance->id)->status->code)->toBe(AttendanceStatus::EXPECTED);
});

test('deleting an event deletes its status history and its attendances history', function () {
    $course = $this->club->createCourse('2026-10-01');
    $course->setAttendance([$this->tom->id], AttendanceStatus::PRESENT);

    $course->delete();

    expect(DB::table('event_status_changes')->count())->toBe(0)
        ->and(DB::table('attendance_status_changes')->count())->toBe(0);
});
