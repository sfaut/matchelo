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
    $this->player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard', 'elo' => 1190]);
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

test('the dashboard shows empty states while sessions and tournaments do not exist', function () {
    $response = $this->actingAs($this->animator)->get(route('clubs.dashboard', $this->club));

    $response->assertSee(e(__('clubs.dashboard.no_session')), false);
    $response->assertSee(__('clubs.dashboard.no_tournament'));
    $response->assertSee(__('clubs.dashboard.nothing_to_handle'));
    $response->assertSee(__('clubs.dashboard.no_sessions_yet'));
});

test('the club ranking lists the best members by ELO', function () {
    $ines = createUser(['email' => 'ines.kaci@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci', 'elo' => 1302]);
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
