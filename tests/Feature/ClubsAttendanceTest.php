<?php

use App\Models\Event;
use App\Models\User\Role;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    Carbon::setTestNow('2026-10-01 10:00');

    $this->animator = createUser(['first_name' => 'Marie', 'last_name' => 'Durand', 'email' => 'm.durand@example.org']);
    $this->tom = createUser(['email' => 'tom@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->ines = createUser(['email' => 'ines@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [Role::ANIMATOR => $this->animator, Role::PLAYER => $this->tom]);
    $this->club->members()->attach($this->ines, ['role_id' => Role::idFor(Role::PLAYER)]);
    $this->actingAs($this->animator);
});

/**
 * Séance du club à une date, ou null.
 */
function sessionOn(string $date): ?Event
{
    return Event::whereDate('scheduled_at', $date)->first();
}

test('only animators and administrators can take the roll call', function () {
    $this->get(route('clubs.attendance', $this->club))->assertOk();
    $this->actingAs($this->tom)->get(route('clubs.attendance', $this->club))->assertForbidden();
});

test('the roll call offers today then the four previous sessions', function () {
    foreach (['2026-08-27', '2026-09-03', '2026-09-10', '2026-09-17', '2026-09-24', '2026-10-08'] as $date) {
        createSession($this->club, $date);
    }

    Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->assertSet('date', '2026-10-01')
        ->assertSet('availableDates', ['2026-10-01', '2026-09-24', '2026-09-17', '2026-09-10', '2026-09-03']);
});

test('a date that is not offered falls back to today', function () {
    Livewire::withQueryParams(['date' => '2025-01-01'])
        ->test('pages::clubs.attendance', ['club' => $this->club])
        ->assertSet('date', '2026-10-01');
});

test('checking a member in creates the session of the day, checking again removes them', function () {
    $component = Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->call('toggle', $this->tom->id)
        ->assertSee(__('clubs.attendance.present'));

    expect(sessionOn('2026-10-01')->attendees->pluck('id')->all())->toBe([$this->tom->id]);

    $component->call('toggle', $this->tom->id);

    expect(sessionOn('2026-10-01')->attendees)->toBeEmpty();
});

test('a user outside the club cannot be checked in', function () {
    $outsider = createUser(['email' => 'out@example.org', 'first_name' => 'Out', 'last_name' => 'Sider']);

    Livewire::test('pages::clubs.attendance', ['club' => $this->club])->call('toggle', $outsider->id);

    expect(sessionOn('2026-10-01')?->attendees ?? collect())->toBeEmpty();
});

test('everyone can be checked in at once, then the roll call reset', function () {
    $component = Livewire::test('pages::clubs.attendance', ['club' => $this->club])->call('markAll');

    expect(sessionOn('2026-10-01')->attendees)->toHaveCount(3);

    $component->call('resetAttendance');

    expect(sessionOn('2026-10-01'))->not->toBeNull()
        ->and(sessionOn('2026-10-01')->attendees)->toBeEmpty();
});

test('a previous session can be corrected', function () {
    createSession($this->club, '2026-09-24', [$this->tom]);

    Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->call('selectDate', '2026-09-24')
        ->assertSet('date', '2026-09-24')
        ->call('toggle', $this->ines->id);

    expect(sessionOn('2026-09-24')->attendees->pluck('id')->all())->toEqualCanonicalizing([$this->tom->id, $this->ines->id])
        ->and(sessionOn('2026-10-01'))->toBeNull();
});

test('a date that is not offered cannot be selected', function () {
    Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->call('selectDate', '2026-01-01')
        ->assertSet('date', '2026-10-01');
});

test('the search filters the members and each row shows the past attendance', function () {
    createSession($this->club, '2026-09-17', [$this->tom]);
    createSession($this->club, '2026-09-24', [$this->tom, $this->ines]);

    Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->assertSee(__('clubs.attendance.rate', ['count' => 2, 'total' => 2]))
        ->set('search', 'kac')
        ->assertSee('Inès Kaci')
        ->assertDontSee('Tom Bernard');
});

test('finishing the roll call goes back to the dashboard with the count', function () {
    Livewire::test('pages::clubs.attendance', ['club' => $this->club])
        ->call('toggle', $this->tom->id)
        ->call('toggle', $this->ines->id)
        ->call('done')
        ->assertRedirectToRoute('clubs.dashboard', $this->club);

    expect(session('toast'))->toBe(trans_choice('clubs.attendance.saved', 2, ['count' => 2]));
});

test('the management navigation leads to the roll call', function () {
    $this->get(route('clubs.dashboard', $this->club))
        ->assertSee('href="'.route('clubs.attendance', $this->club).'"', false)
        ->assertSee(e(__('components.app_shell.sections.attendance')), false);
});
