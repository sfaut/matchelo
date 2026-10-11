<?php

use App\Models\AttendanceStatus;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User;
use App\Models\User\Role;
use App\Support\EventPlanner;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed();

    $this->paul = createUser(['email' => 'paul@example.org', 'first_name' => 'Paul', 'last_name' => 'Roche']);
    $this->lea = createUser();
    $this->tom = createUser(['email' => 'tom@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->ines = createUser(['email' => 'ines@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [
        Role::PRESIDENT => $this->paul,
        Role::ANIMATOR => $this->lea,
        Role::PLAYER => $this->tom,
    ]);
    $this->club->members()->attach($this->ines, ['role_id' => Role::idFor(Role::PLAYER)]);
    $this->planner = new EventPlanner($this->club);
});

/**
 * Génère des cours avec un animateur et un joueur.
 *
 * @param  list<string>  $dates  horodatages des cours
 * @return list<int> identifiants des cours
 */
function generateCourses(EventPlanner $planner, User $animator, User $player, array $dates): array
{
    return $planner->generate($dates, EventType::COURSE, [$animator->id], [$player->id])->modelKeys();
}

test('the occurrences fall on the chosen weekdays between two dates included, at the chosen time', function () {
    $format = fn (array $occurrences): array => array_map(fn (CarbonImmutable $occurrence): string => $occurrence->format('Y-m-d H:i'), $occurrences);

    expect($format($this->planner->occurrences('2026-10-01', '2026-10-31', [2], '17:30')))
        ->toBe(['2026-10-06 17:30', '2026-10-13 17:30', '2026-10-20 17:30', '2026-10-27 17:30'])
        ->and($format($this->planner->occurrences('2026-10-01', '2026-10-08', [2, 4], '12:30')))
        ->toBe(['2026-10-01 12:30', '2026-10-06 12:30', '2026-10-08 12:30']);
});

test('generating creates independent scheduled events, each with its animators and players expected', function () {
    $events = $this->planner->generate(['2026-10-06 17:30', '2026-10-13 17:30'], EventType::COURSE, [$this->lea->id], [$this->tom->id, $this->ines->id], 'Ouverture italienne');

    expect($events)->toHaveCount(2)
        ->and($events->map(fn (Event $event): string => $event->scheduled_at->format('Y-m-d H:i'))->all())->toBe(['2026-10-06 17:30', '2026-10-13 17:30']);

    foreach ($events as $event) {
        expect($event->type->code)->toBe(EventType::COURSE)
            ->and($event->status->code)->toBe(EventStatus::SCHEDULED)
            ->and($event->description)->toBe('Ouverture italienne')
            ->and(eventRoleCodes($event->id))->toEqual([
                $this->lea->id => Role::ANIMATOR,
                $this->tom->id => Role::PLAYER,
                $this->ines->id => Role::PLAYER,
            ])
            ->and($event->participants()->wherePivot('attendance_status_id', AttendanceStatus::idFor(AttendanceStatus::EXPECTED))->count())->toBe(3);
    }
});

test('generating courses needs at least one animator and one player, and writes nothing otherwise', function (Closure $participant_ids) {
    expect(fn () => $this->planner->generate(['2026-10-06 17:30', '2026-10-13 17:30'], EventType::COURSE, ...$participant_ids->call($this)))
        ->toThrow(InvalidArgumentException::class);

    expect(Event::count())->toBe(0)
        ->and(DB::table('attendances')->count())->toBe(0);
})->with([
    'no animator' => [fn (): array => [[], [$this->tom->id]]],
    'no player' => [fn (): array => [[$this->lea->id], []]],
]);

test('a tournament may be generated without animator', function () {
    $events = $this->planner->generate(['2026-11-14 14:00'], EventType::TOURNAMENT, [], [$this->tom->id, $this->ines->id]);

    expect($events->first()->players)->toHaveCount(2);
});

test('updating events changes the time, status and description given, and keeps the others', function () {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30', '2026-10-13 17:30']);

    $this->planner->update($event_ids, time: '18:00', status_code: EventStatus::CANCELLED);

    expect(Event::findMany($event_ids)->map(fn (Event $event): array => [$event->scheduled_at->format('Y-m-d H:i'), $event->status->code, $event->description])->all())
        ->toBe([['2026-10-06 18:00', EventStatus::CANCELLED, ''], ['2026-10-13 18:00', EventStatus::CANCELLED, '']]);

    $this->planner->update([$event_ids[0]], description: 'Finales');

    expect(Event::find($event_ids[0]))
        ->description->toBe('Finales')
        ->scheduled_at->format('H:i')->toBe('18:00');
});

test('deleting events deletes their attendances', function () {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30', '2026-10-13 17:30', '2026-10-20 17:30']);

    expect($this->planner->delete(array_slice($event_ids, 0, 2)))->toBe(2)
        ->and(Event::pluck('id')->all())->toBe([$event_ids[2]])
        ->and(DB::table('attendances')->distinct()->pluck('event_id')->all())->toBe([$event_ids[2]]);
});

test('an event of another club is refused, and nothing is changed', function (Closure $operation) {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30']);
    $other_club = createClubWith('Échiquier Nord', [Role::PRESIDENT => createUser(['email' => 'a@example.org'])]);
    $other_event = $other_club->createCourse('2026-10-06');

    expect(fn () => $operation->call($this, [$event_ids[0], $other_event->id]))->toThrow(ModelNotFoundException::class);

    expect(Event::count())->toBe(2)
        ->and(Event::find($event_ids[0])->description)->toBe('')
        ->and(eventRoleCodes($event_ids[0]))->toEqual([$this->lea->id => Role::ANIMATOR, $this->tom->id => Role::PLAYER]);
})->with([
    'update' => [fn (array $event_ids) => $this->planner->update($event_ids, description: 'Finales')],
    'delete' => [fn (array $event_ids) => $this->planner->delete($event_ids)],
    'enrol' => [fn (array $event_ids) => $this->planner->enrol($event_ids, [$this->ines->id], Role::PLAYER)],
    'withdraw' => [fn (array $event_ids) => $this->planner->withdraw($event_ids, [$this->lea->id])],
]);

test('enrolling adds newcomers as expected and changes the role of members already enrolled', function () {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30', '2026-10-13 17:30']);
    Event::find($event_ids[0])->setAttendance([$this->tom->id], AttendanceStatus::PRESENT);

    $this->planner->enrol($event_ids, [$this->ines->id], Role::PLAYER);
    $this->planner->enrol($event_ids, [$this->tom->id, $this->paul->id], Role::ANIMATOR);
    $this->planner->enrol($event_ids, [$this->ines->id], Role::PLAYER);

    foreach ($event_ids as $event_id) {
        expect(eventRoleCodes($event_id))->toEqual([
            $this->lea->id => Role::ANIMATOR,
            $this->tom->id => Role::ANIMATOR,
            $this->ines->id => Role::PLAYER,
            $this->paul->id => Role::ANIMATOR,
        ]);
    }

    expect(Event::find($event_ids[0])->attendees->pluck('id')->all())->toBe([$this->tom->id]);
});

test('withdrawing removes members with their attendance', function () {
    $event_ids = $this->planner->generate(['2026-10-06 17:30', '2026-10-13 17:30'], EventType::COURSE, [$this->lea->id], [$this->tom->id, $this->ines->id])->modelKeys();

    $this->planner->withdraw($event_ids, [$this->ines->id]);

    expect(DB::table('attendances')->where('user_id', $this->ines->id)->count())->toBe(0)
        ->and(DB::table('attendances')->count())->toBe(4);
});

test('a course losing its last animator or player cancels the whole operation', function (Closure $operation) {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30', '2026-10-13 17:30']);
    $this->planner->enrol([$event_ids[0]], [$this->ines->id], Role::PLAYER);

    expect(fn () => $operation->call($this, $event_ids))->toThrow(InvalidArgumentException::class);

    expect(eventRoleCodes($event_ids[0]))->toEqual([$this->lea->id => Role::ANIMATOR, $this->tom->id => Role::PLAYER, $this->ines->id => Role::PLAYER])
        ->and(eventRoleCodes($event_ids[1]))->toEqual([$this->lea->id => Role::ANIMATOR, $this->tom->id => Role::PLAYER]);
})->with([
    'last player withdrawn' => [fn (array $event_ids) => $this->planner->withdraw($event_ids, [$this->tom->id])],
    'last animator turned player' => [fn (array $event_ids) => $this->planner->enrol($event_ids, [$this->lea->id], Role::PLAYER)],
]);

test('the participants of a finalized event cannot change', function () {
    $event_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-06 17:30']);
    $this->planner->update($event_ids, status_code: EventStatus::FINALIZED);

    expect(fn () => $this->planner->enrol($event_ids, [$this->ines->id], Role::PLAYER))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->planner->withdraw($event_ids, [$this->tom->id]))->toThrow(InvalidArgumentException::class);

    expect(eventRoleCodes($event_ids[0]))->toEqual([$this->lea->id => Role::ANIMATOR, $this->tom->id => Role::PLAYER]);
});

test('copying participants enrols them with their role and keeps those already in place', function () {
    [$source_id] = $this->planner->generate(['2026-10-06 17:30'], EventType::COURSE, [$this->paul->id], [$this->tom->id, $this->ines->id])->modelKeys();
    $target_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-13 17:30', '2026-10-20 17:30']);

    $this->planner->copyParticipants(Event::find($source_id), $target_ids);

    foreach ($target_ids as $target_id) {
        expect(eventRoleCodes($target_id))->toEqual([
            $this->lea->id => Role::ANIMATOR,
            $this->tom->id => Role::PLAYER,
            $this->paul->id => Role::ANIMATOR,
            $this->ines->id => Role::PLAYER,
        ]);
    }
});

test('the participants of an event of another club cannot be copied', function () {
    $target_ids = generateCourses($this->planner, $this->lea, $this->tom, ['2026-10-13 17:30']);
    $other_club = createClubWith('Échiquier Nord', [Role::PRESIDENT => createUser(['email' => 'a@example.org'])]);

    expect(fn () => $this->planner->copyParticipants($other_club->createCourse('2026-10-06'), $target_ids))
        ->toThrow(ModelNotFoundException::class);
});
