<?php

use App\Models\User\Role;

beforeEach(function () {
    $this->seed();
});

test('guests are sent to the login page', function () {
    $this->get(route('clubs.index'))->assertRedirect(route('login'));
});

test('the player home leads to the clubs list', function () {
    $this->actingAs(createUser())
        ->get(route('player.home'))
        ->assertRedirect(route('clubs.index'));
});

test('a user without club sees an invitation to create one', function () {
    $response = $this->actingAs(createUser())->get(route('clubs.index'));

    $response->assertOk();
    $response->assertSee(__('clubs.index.title'));
    $response->assertSee(e(__('clubs.index.empty')), false);
    $response->assertSee('href="'.route('clubs.create').'"', false);
});

test('a user sees their clubs with the member count and a teacher badge where they animate', function () {
    $user = createUser();
    $other = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    createClubWith('Échecs · Collège Jean-Moulin', [Role::ANIMATOR => $user, Role::PLAYER => $other]);
    createClubWith('Échiquier Nord', [Role::PLAYER => $user]);
    createClubWith('Club voisin', [Role::ANIMATOR => $other]);

    $response = $this->actingAs($user)->get(route('clubs.index'));

    $response->assertSeeInOrder(['Échecs · Collège Jean-Moulin', __('clubs.index.animator_badge'), trans_choice('clubs.index.members', 2)]);
    $response->assertSeeInOrder(['Échiquier Nord', trans_choice('clubs.index.members', 1)]);
    $response->assertDontSee('Club voisin');
    $response->assertDontSee(e(__('clubs.index.empty')), false);
});

test('the shell shows the user initials, the scheme toggle and a logout form', function () {
    $response = $this->actingAs(createUser())->get(route('clubs.index'));

    expect($response->getContent())->toMatch('/title="[^"]*Léa Martin"[^>]*>\s*LM\s*</');
    $response->assertSee('x-on:click="$store.scheme.toggle()"', false);
    $response->assertSee('action="'.route('logout').'"', false);
});

test('the mode switch is shown only to a user who animates a club', function () {
    $animator = createUser();
    $player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    createClubWith('Échecs · Collège Jean-Moulin', [Role::ANIMATOR => $animator, Role::PLAYER => $player]);

    $this->actingAs($animator)->get(route('clubs.index'))
        ->assertSee('href="'.route('club.dashboard').'"', false);

    $this->actingAs($player)->get(route('clubs.index'))
        ->assertDontSee('href="'.route('club.dashboard').'"', false);
});

test('the clubs page shows no untranslated key in any supported language', function () {
    $user = createUser();
    createClubWith('Échiquier Nord', [Role::ANIMATOR => $user]);

    foreach (config('matchelo.locales') as $locale) {
        $response = $this->actingAs($user)->withHeader('Accept-Language', $locale)->get(route('clubs.index'));

        $response->assertOk();
        $response->assertDontSee('clubs.index.', false);
        $response->assertDontSee('components.', false);
    }
});
