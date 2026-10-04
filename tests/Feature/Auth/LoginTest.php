<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
});

test('the login page renders for a guest', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee(__('auth_pages.login.title'));
    $response->assertSee('href="'.route('password.request').'"', false);
    $response->assertSee('href="'.route('register').'"', false);
    $response->assertSee('aria-controls="password"', false);
});

test('after registering, the email is prefilled with a confirmation', function () {
    session()->flash('registered_email', 'tom.bernard@example.org');

    Livewire::test('pages::auth.login')
        ->assertSet('email', 'tom.bernard@example.org')
        ->assertSee(__('auth_pages.login.registered', ['email' => 'tom.bernard@example.org']));
});

test('valid credentials log the user in and go to their clubs', function () {
    $user = createUser();

    Livewire::test('pages::auth.login')
        ->set('email', 'lea.martin@example.org')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('clubs.index'));

    expect(Auth::id())->toBe($user->id);
});

test('the email is matched whatever its case and surrounding spaces', function () {
    createUser();

    Livewire::test('pages::auth.login')
        ->set('email', '  Lea.Martin@Example.ORG ')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasNoErrors();

    expect(Auth::check())->toBeTrue();
});

test('the user goes back to the page requested before logging in', function () {
    createUser();
    session()->put('url.intended', route('club.dashboard'));

    Livewire::test('pages::auth.login')
        ->set('email', 'lea.martin@example.org')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertRedirect(route('club.dashboard'));
});

test('the first login confirms the email address, later ones keep the date', function () {
    $user = createUser();
    $login = fn () => Livewire::test('pages::auth.login')
        ->set('email', 'lea.martin@example.org')
        ->set('password', 'secret-password')
        ->call('login');

    $login();
    $verified_at = $user->refresh()->email_verified_at;

    Auth::logout();
    $this->travel(1)->day();
    $login();

    expect($verified_at)->not->toBeNull()
        ->and($user->refresh()->email_verified_at->equalTo($verified_at))->toBeTrue();
});

test('wrong credentials are rejected without logging in', function () {
    createUser();

    Livewire::test('pages::auth.login')
        ->set('email', 'lea.martin@example.org')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email')
        ->assertSee(__('auth.failed'))
        ->assertSet('password', '');

    expect(Auth::check())->toBeFalse();
});

test('both fields are required and the email must be valid', function () {
    Livewire::test('pages::auth.login')
        ->call('login')
        ->assertHasErrors(['email' => 'required', 'password' => 'required']);

    Livewire::test('pages::auth.login')
        ->set('email', 'not-an-email')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasErrors(['email' => 'email']);
});

test('too many failed attempts lock the login, even with the right password', function () {
    createUser();

    foreach (range(1, 5) as $attempt) {
        Livewire::test('pages::auth.login')
            ->set('email', 'lea.martin@example.org')
            ->set('password', 'wrong-password')
            ->call('login');
    }

    $component = Livewire::test('pages::auth.login')
        ->set('email', 'lea.martin@example.org')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertHasErrors('email');

    expect($component->errors()->first('email'))->toStartWith(Str::before(__('auth.throttle'), ':seconds'))
        ->and(Auth::check())->toBeFalse();
});

test('a logged in user is sent away from the login page', function () {
    $this->actingAs(createUser())
        ->get(route('login'))
        ->assertRedirect(route('clubs.index'));
});
