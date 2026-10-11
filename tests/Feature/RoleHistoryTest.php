<?php

use App\Models\AttendanceRoleChange;
use App\Models\EventType;
use App\Models\Membership;
use App\Models\MembershipRoleChange;
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
 * Résume un historique de rôles : code du rôle, horodatage du changement et auteur.
 *
 * @param  iterable<MembershipRoleChange|AttendanceRoleChange>  $changes  changements de rôle (relation roleChanges)
 * @return list<array{string, string, int}>
 */
function roleHistory(iterable $changes): array
{
    return collect($changes)->map(fn (MembershipRoleChange|AttendanceRoleChange $change): array => [$change->role->code, $change->changed_at->format('Y-m-d H:i'), $change->user_id])->all();
}

test('a membership records its initial role, then each change with its timestamp and author', function () {
    $this->actingAs($this->paul);
    $this->travelTo(Carbon::parse('2026-10-15 18:20:00'));

    $this->tom_membership->changeRole(Role::REFEREE);
    $this->tom_membership->changeRole(Role::REFEREE);

    expect(roleHistory($this->tom_membership->fresh()->roleChanges))->toBe([
        [Role::PLAYER, '2026-10-01 10:00', $this->paul->id],
        [Role::REFEREE, '2026-10-15 18:20', $this->paul->id],
    ]);
});

test('a role change through the members relation is recorded too', function () {
    $this->actingAs($this->lea);

    $this->club->members()->updateExistingPivot($this->tom->id, ['role_id' => Role::idFor(Role::ANIMATOR)]);

    expect(roleHistory($this->tom_membership->fresh()->roleChanges))->toBe([
        [Role::PLAYER, '2026-10-01 10:00', $this->paul->id],
        [Role::ANIMATOR, '2026-10-01 10:00', $this->lea->id],
    ]);
});

test('a membership joined before the application records its initial status and role at its joining date', function () {
    $membership = Membership::create([
        'club_id' => $this->club->id,
        'user_id' => createUser(['email' => 'ines@example.org'])->id,
        'role_id' => Role::idFor(Role::PLAYER),
        'joined_at' => '2024-09-12',
    ]);

    expect(roleHistory($membership->roleChanges))->toBe([[Role::PLAYER, '2024-09-12 00:00', $this->paul->id]])
        ->and($membership->statusChanges->first()->changed_at->format('Y-m-d H:i'))->toBe('2024-09-12 00:00');
});

test('an attendance records its initial role, then each change made by enrolling', function () {
    $planner = new EventPlanner($this->club);
    $course = $planner->generate(['2026-10-06 17:30'], EventType::COURSE, [$this->lea->id], [$this->tom->id])->first();
    $lea_attendance = $course->attendances()->where('user_id', $this->lea->id)->firstOrFail();

    $this->actingAs($this->paul);
    $this->travelTo(Carbon::parse('2026-10-02 09:00:00'));
    $planner->enrol([$course->id], [$this->paul->id], Role::ANIMATOR);
    $planner->enrol([$course->id], [$this->lea->id], Role::PLAYER);

    expect(roleHistory($lea_attendance->roleChanges))->toBe([
        [Role::ANIMATOR, '2026-10-01 10:00', $this->paul->id],
        [Role::PLAYER, '2026-10-02 09:00', $this->paul->id],
    ])
        ->and(DB::table('attendance_role_changes')->count())->toBe(4);
});

test('deleting an event deletes the role history of its attendances', function () {
    $course = $this->club->createCourse('2026-10-01');

    $course->delete();

    expect(DB::table('attendance_role_changes')->count())->toBe(0);
});
