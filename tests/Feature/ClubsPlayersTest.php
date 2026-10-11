<?php

use App\Models\User;
use App\Models\User\Role;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();

    $this->animator = createUser(['first_name' => 'Marie', 'last_name' => 'Durand', 'email' => 'm.durand@example.org']);
    $this->club = createClubWith('Échecs · Collège Jean-Moulin', [Role::ANIMATOR => $this->animator]);
    $this->actingAs($this->animator);
});

test('only animators and administrators can open the add students page', function () {
    $player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->club->members()->attach($player, ['role_id' => Role::idFor(Role::PLAYER)]);

    $this->get(route('clubs.players.create', $this->club))->assertOk();
    $this->actingAs($player)->get(route('clubs.players.create', $this->club))->assertForbidden();
});

test('the search finds players outside the club by first and last name', function () {
    $other_club = createClubWith('Échiquier Nord', [Role::ANIMATOR => createUser(['email' => 'a@example.org', 'first_name' => 'Anne', 'last_name' => 'Roy'])]);
    $lea = createUser(['email' => 'lea@example.org', 'first_name' => 'Léa', 'last_name' => 'Martin', 'elo_rating' => 1248]);
    $other_club->members()->attach($lea, ['role_id' => Role::idFor(Role::PLAYER)]);
    createUser(['email' => 'leon@example.org', 'first_name' => 'Léon', 'last_name' => 'Petit']);

    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->set('search', 'léa mar')
        ->assertSee('Léa Martin · '.Number::format(1248))
        ->assertSee('Échiquier Nord')
        ->assertDontSee('Léon Petit');
});

test('the search ignores members of the club and too short queries', function () {
    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->set('search', 'Durand')
        ->assertDontSee(__('clubs.players.already_on_matchelo'))
        ->assertSee(__('clubs.players.no_result'))
        ->set('search', 'D')
        ->assertDontSee(__('clubs.players.no_result'));
});

test('the search treats LIKE wildcards as plain text', function () {
    createUser(['email' => 'lea@example.org', 'first_name' => 'Léa', 'last_name' => 'Martin']);

    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->set('search', '%%')
        ->assertDontSee('Léa Martin');
});

test('adding a found player makes them a player of the club', function () {
    $lea = createUser(['email' => 'lea@example.org', 'first_name' => 'Léa', 'last_name' => 'Martin']);

    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->call('add', $lea->id)
        ->assertRedirectToRoute('clubs.players.create', $this->club);

    expect($this->club->members()->whereKey($lea->id)->first()->membership->role_id)->toBe(Role::idFor(Role::PLAYER))
        ->and(session('toast'))->toBe(__('clubs.players.added', ['name' => 'Léa Martin']));
});

test('adding a player twice keeps a single membership', function () {
    $lea = createUser(['email' => 'lea@example.org', 'first_name' => 'Léa', 'last_name' => 'Martin']);

    Livewire::test('pages::clubs.players.create', ['club' => $this->club])->call('add', $lea->id);
    Livewire::test('pages::clubs.players.create', ['club' => $this->club])->call('add', $lea->id);

    expect($this->club->members()->whereKey($lea->id)->count())->toBe(1);
});

test('creating a player makes a player without account in the club, with the starting ELO', function () {
    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->set('lastName', ' Diallo ')
        ->set('firstName', ' Nathan ')
        ->set('startingElo', '1050')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('clubs.players.create', $this->club);

    $nathan = User::where('last_name', 'Diallo')->firstOrFail();

    expect($nathan->first_name)->toBe('Nathan')
        ->and($nathan->elo_rating)->toBe(1050)
        ->and($nathan->email)->toBeNull()
        ->and($nathan->hasAccount())->toBeFalse()
        ->and($this->club->members()->whereKey($nathan->id)->exists())->toBeTrue()
        ->and(session('toast'))->toBe(__('clubs.players.created', ['name' => 'Nathan Diallo']));
});

test('several players without account can coexist', function () {
    foreach (['Diallo', 'Moreau'] as $last_name) {
        Livewire::test('pages::clubs.players.create', ['club' => $this->club])
            ->set('lastName', $last_name)
            ->set('firstName', 'Sam')
            ->call('create')
            ->assertHasNoErrors();
    }

    expect(User::whereNull('email')->count())->toBe(2);
});

test('a new player is validated', function (string $field, string $value, string $rule) {
    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->set([
            'lastName' => 'Diallo',
            'firstName' => 'Nathan',
            'startingElo' => '1200',
            $field => $value,
        ])
        ->call('create')
        ->assertHasErrors([$field => $rule]);
})->with([
    'missing last name' => ['lastName', '', 'required'],
    'first name too long' => ['firstName', str_repeat('a', 31), 'max'],
    'ELO too low' => ['startingElo', '99', 'between'],
    'ELO too high' => ['startingElo', '3001', 'between'],
    'ELO not a number' => ['startingElo', 'abc', 'integer'],
]);

test('the page lists the club members, flagging players without account', function () {
    $nathan = User::create(['last_name' => 'Diallo', 'first_name' => 'Nathan']);
    $this->club->members()->attach($nathan, ['role_id' => Role::idFor(Role::PLAYER)]);

    Livewire::test('pages::clubs.players.create', ['club' => $this->club])
        ->assertSee(trans_choice('clubs.players.members', 2))
        ->assertSeeInOrder(['Nathan Diallo', __('clubs.players.no_account')])
        ->assertSeeInOrder(['Marie Durand', __('clubs.index.animator_badge')]);
});
