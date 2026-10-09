<?php

use App\Models\User;
use App\Models\User\Role;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed();

    $this->animator = createUser([
        'first_name' => 'Marie',
        'last_name' => 'Durand',
        'email' => 'm.durand@example.org',
    ]);
    $this->player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard', 'elo_rating' => 1190]);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [Role::ANIMATOR => $this->animator, Role::PLAYER => $this->player]);
});

test('the management entry opens the dashboard of the first animated club', function () {
    createClubWith('Atelier du jeudi', [Role::ANIMATOR => $this->animator]);
    $first_club = $this->animator->clubs()->orderBy('name')->first();

    $this->actingAs($this->animator)
        ->get(route('manage.home'))
        ->assertRedirect(route('clubs.dashboard', $first_club));
});

test('the management entry sends a non animator back to their clubs', function () {
    $this->actingAs($this->player)
        ->get(route('manage.home'))
        ->assertRedirect(route('clubs.index'));
});

test('only animators and administrators can open the dashboard', function (string $who, int $status) {
    $user = match ($who) {
        'animator' => $this->animator,
        'player' => $this->player,
        'administrator' => tap(createUser(['email' => 'admin@example.org']), fn (User $admin) => $admin->forceFill(['is_administrator' => true])->save()),
    };

    $this->actingAs($user)->get(route('clubs.dashboard', $this->club))->assertStatus($status);
})->with([
    'animator' => ['animator', 200],
    'administrator' => ['administrator', 200],
    'player' => ['player', 403],
]);

test('the dashboard greets the animator with the date of the day', function () {
    Carbon::setTestNow('2026-10-01 10:00');

    $response = $this->actingAs($this->animator)->withHeader('Accept-Language', 'fr')->get(route('clubs.dashboard', $this->club));

    $response->assertSee('Jeudi 1er octobre');
    $response->assertSee('Bonjour Marie Durand');
    $response->assertSee('href="'.route('clubs.players.create', $this->club).'"', false);
});

test('without any session, the dashboard invites to take the roll call and shows empty states', function () {
    $response = $this->actingAs($this->animator)->get(route('clubs.dashboard', $this->club));

    $response->assertSee(trans_choice('clubs.attendance.present_out_of', 2, ['total' => 2]));
    $response->assertSee(trans_choice('clubs.dashboard.not_checked', 2, ['count' => 2]));
    $response->assertSee(e(__('clubs.dashboard.take_attendance')), false);
    $response->assertSee('href="'.route('clubs.attendance', $this->club).'"', false);
    $response->assertDontSee(__('clubs.dashboard.in_progress'));
    $response->assertSee(__('clubs.dashboard.no_tournament'));
    $response->assertSee(__('clubs.dashboard.nothing_to_handle'));
    $response->assertSee(__('clubs.dashboard.no_sessions_yet'));
});

test('the dashboard reflects the roll call, the attendance and the members to follow up', function () {
    Carbon::setTestNow('2026-10-01 10:00');
    createSession($this->club, '2026-09-17', [$this->animator]);
    createSession($this->club, '2026-09-24', [$this->animator]);
    createSession($this->club, '2026-10-01', [$this->animator, $this->player]);

    $response = $this->actingAs($this->animator)->withHeader('Accept-Language', 'fr')->get(route('clubs.dashboard', $this->club));

    $response->assertSee(__('clubs.dashboard.in_progress'));
    $response->assertSee(e(__('clubs.dashboard.edit_attendance')), false);
    $response->assertSee(trans_choice('clubs.dashboard.not_checked', 0, ['count' => 0]));
    // Taux : 50 % les 17 et 24/9, 100 % aujourd'hui, moyenne 67 %
    $response->assertSeeInOrder([Number::percentage(50), Number::percentage(50), Number::percentage(100)]);
    $response->assertSeeInOrder(['17/9', '24/9', __('clubs.dashboard.today_short')]);
    $response->assertSee(__('clubs.dashboard.average', ['rate' => Number::percentage(67)]));
    $response->assertSeeInOrder(['Tom Bernard', trans_choice('clubs.dashboard.absent_in_a_row', 2, ['count' => 2])]);
});

test('the club ranking lists the best members by ELO', function () {
    $ines = createUser(['email' => 'ines.kaci@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci', 'elo_rating' => 1302]);
    $this->club->members()->attach($ines, ['role_id' => Role::idFor(Role::PLAYER)]);

    $response = $this->actingAs($this->animator)->get(route('clubs.dashboard', $this->club));

    $response->assertSeeInOrder(['Inès Kaci', Number::format(1302), 'Marie Durand', Number::format(1200), 'Tom Bernard', Number::format(1190)]);
    $response->assertSee(trans_choice('clubs.index.members', 3));
});

test('the management shell shows the club selector, the management navigation and the invite code', function () {
    $other_club = createClubWith('Atelier du jeudi', [Role::ANIMATOR => $this->animator]);

    $response = $this->actingAs($this->animator)->get(route('clubs.dashboard', $this->club));

    $response->assertSee('href="'.route('clubs.dashboard', $other_club).'"', false);
    $response->assertSee('href="'.route('clubs.players.create', $this->club).'"', false);
    $response->assertSee('href="'.route('clubs.edit', $this->club).'"', false);
    $response->assertSee(__('components.app_shell.invite_code'));
    $response->assertSee($this->club->code);
    expect($response->getContent())->toMatch('/class="mx-nav-item"\s+aria-current="page"\s*>\s*'.preg_quote(__('components.app_shell.sections.dashboard'), '/').'/');
});

test('the mode switch leads to the management entry', function () {
    $this->actingAs($this->animator)
        ->get(route('clubs.index'))
        ->assertSee('href="'.route('manage.home').'"', false);
});
