<?php

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use App\Support\ClubLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    Storage::fake('public');

    $this->animator = createUser();
    $this->player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $this->outsider = createUser(['email' => 'ines.kaci@example.org', 'first_name' => 'Inès', 'last_name' => 'Kaci']);
    $this->club = createClubWith('Échiquier Nord', [Role::ANIMATOR => $this->animator, Role::PLAYER => $this->player]);
    $this->club->update(['city' => 'Lille', 'description' => 'Mercredi 14h']);
});

/**
 * Donne à un club un logo enregistré sur le disque public factice.
 */
function giveClubLogo(Club $club): string
{
    $path = app(ClubLogo::class)->store(UploadedFile::fake()->image('old.png', 300, 300), $club);
    $club->update(['logo_path' => $path]);

    return $path;
}

/**
 * Rend un utilisateur administrateur (is_administrator n'est pas assignable en masse).
 */
function makeAdministrator(User $user): User
{
    $user->forceFill(['is_administrator' => true])->save();

    return $user;
}

test('the club policy lets its animators and administrators update it', function (string $who, bool $allowed) {
    $user = match ($who) {
        'animator' => $this->animator,
        'player' => $this->player,
        'outsider' => $this->outsider,
        'administrator' => makeAdministrator($this->outsider),
    };

    expect($user->fresh()->can('update', $this->club))->toBe($allowed);
})->with([
    'animator of the club' => ['animator', true],
    'player of the club' => ['player', false],
    'outsider' => ['outsider', false],
    'administrator' => ['administrator', true],
]);

test('guests are sent to the login page', function () {
    $this->get(route('clubs.edit', $this->club))->assertRedirect(route('login'));
});

test('only animators and administrators can open the edit page', function (string $who, int $status) {
    $user = $who === 'administrator' ? makeAdministrator($this->outsider) : $this->{$who};

    $this->actingAs($user->fresh())->get(route('clubs.edit', $this->club))->assertStatus($status);
})->with([
    'animator' => ['animator', 200],
    'administrator' => ['administrator', 200],
    'player' => ['player', 403],
    'outsider' => ['outsider', 403],
]);

test('the edit page is prefilled and shows the invite code', function () {
    $this->actingAs($this->animator);

    Livewire::test('pages::clubs.edit', ['club' => $this->club])
        ->assertSet('form.name', 'Échiquier Nord')
        ->assertSet('form.city', 'Lille')
        ->assertSet('form.description', 'Mercredi 14h')
        ->assertSee($this->club->code);
});

test('saving updates the club, keeps its code and goes back with a toast', function () {
    $this->actingAs($this->animator);
    $code = $this->club->code;

    Livewire::test('pages::clubs.edit', ['club' => $this->club])
        ->set('form.name', ' Échiquier du Nord ')
        ->set('form.city', '')
        ->set('form.description', ' Mercredi 14h · salle 3 ')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('clubs.index');

    $club = $this->club->fresh();

    expect($club->name)->toBe('Échiquier du Nord')
        ->and($club->city)->toBe('')
        ->and($club->description)->toBe('Mercredi 14h · salle 3')
        ->and($club->code)->toBe($code)
        ->and(session('toast'))->toBe(__('clubs.edit.updated', ['name' => 'Échiquier du Nord']));
});

test('the edit form validates like the create form', function () {
    $this->actingAs($this->animator);

    Livewire::test('pages::clubs.edit', ['club' => $this->club])
        ->set('form.name', 'E')
        ->set('form.description', str_repeat('a', 301))
        ->call('save')
        ->assertHasErrors(['form.name' => 'min', 'form.description' => 'max']);

    expect($this->club->fresh()->name)->toBe('Échiquier Nord');
});

test('a new logo replaces the old one and deletes its file', function () {
    $this->actingAs($this->animator);
    $old_path = giveClubLogo($this->club);

    Livewire::test('pages::clubs.edit', ['club' => $this->club])
        ->set('form.logo', UploadedFile::fake()->image('new.png', 500, 500))
        ->call('save')
        ->assertHasNoErrors();

    $new_path = $this->club->fresh()->logo_path;

    expect($new_path)->not->toBe($old_path)->not->toBe('');
    Storage::disk('public')->assertMissing($old_path);
    Storage::disk('public')->assertExists($new_path);
});

test('the logo can be removed', function () {
    $this->actingAs($this->animator);
    $old_path = giveClubLogo($this->club);

    Livewire::test('pages::clubs.edit', ['club' => $this->club])
        ->assertSee(__('clubs.form.remove_logo'))
        ->set('form.removeLogo', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($this->club->fresh()->logo_path)->toBe('');
    Storage::disk('public')->assertMissing($old_path);
});

test('saving is refused if the user stopped animating the club meanwhile', function () {
    $this->actingAs($this->animator);
    $component = Livewire::test('pages::clubs.edit', ['club' => $this->club]);

    $this->club->members()->updateExistingPivot($this->animator->id, ['role_id' => Role::idFor(Role::PLAYER)]);

    $component->set('form.name', 'Club détourné')->call('save')->assertForbidden();

    expect($this->club->fresh()->name)->toBe('Échiquier Nord');
});

test('the clubs page offers the edit link only on clubs the user animates', function () {
    $other_club = createClubWith('Club voisin', [Role::ANIMATOR => $this->outsider, Role::PLAYER => $this->animator]);

    $response = $this->actingAs($this->animator)->get(route('clubs.index'));

    $response->assertSee('href="'.route('clubs.edit', $this->club).'"', false);
    $response->assertDontSee('href="'.route('clubs.edit', $other_club).'"', false);
});
