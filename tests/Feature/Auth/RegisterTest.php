<?php

use App\Models\User;
use App\Notifications\AccountCreated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    Notification::fake();
});

/**
 * Prépare le formulaire d'inscription rempli avec des valeurs valides, surchargeables.
 *
 * @param  array<string, string>  $fields  champs à surcharger
 */
function registerForm(array $fields = []): Testable
{
    return Livewire::test('pages::auth.register')->set([
        'lastName' => 'Bernard',
        'firstName' => 'Tom',
        'email' => 'tom.bernard@example.org',
        ...$fields,
    ]);
}

test('the register page renders for a guest', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertSee(__('auth_pages.register.title'));
    $response->assertSeeInOrder([
        __('auth_pages.fields.last_name'),
        __('auth_pages.fields.first_name'),
        __('auth_pages.fields.email'),
    ]);
});

test('registering creates the account, emails a generated password and goes to login', function () {
    registerForm([
        'lastName' => '  Bernard ',
        'firstName' => ' Tom ',
        'email' => ' Tom.Bernard@Example.org ',
    ])
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('login');

    $user = User::where('email', 'tom.bernard@example.org')->firstOrFail();

    expect($user->last_name)->toBe('Bernard')
        ->and($user->first_name)->toBe('Tom')
        ->and($user->email_verified_at)->toBeNull()
        ->and(session('registered_email'))->toBe('tom.bernard@example.org');

    Notification::assertSentTo($user, AccountCreated::class, function (AccountCreated $notification) use ($user): bool {
        $mail = $notification->toMail($user);
        $password = trim(collect($mail->introLines)->last());

        return mb_strlen($password) === 12
            && Hash::check($password, $user->password_hash)
            && $mail->actionUrl === route('login');
    });
});

test('the account email is written in the page language', function () {
    app()->setLocale('fr');
    registerForm()->call('register');

    $user = User::where('email', 'tom.bernard@example.org')->firstOrFail();

    Notification::assertSentTo($user, AccountCreated::class, function (AccountCreated $notification) use ($user): bool {
        $mail = $notification->toMail($user);

        return $mail->subject === 'Votre compte Matchelo' && $mail->greeting === 'Bonjour Tom,';
    });
});

test('every field is required', function () {
    Livewire::test('pages::auth.register')
        ->call('register')
        ->assertHasErrors([
            'lastName' => 'required',
            'firstName' => 'required',
            'email' => 'required',
        ]);

    expect(User::where('email', '!=', 'sebastien.faut@gmail.com')->count())->toBe(0);
    Notification::assertNothingSent();
});

test('invalid values are rejected', function (string $field, string $value, string $rule) {
    registerForm([$field => $value])
        ->call('register')
        ->assertHasErrors([$field => $rule]);
})->with([
    'last name too long' => ['lastName', str_repeat('a', 31), 'max'],
    'first name too long' => ['firstName', str_repeat('a', 31), 'max'],
    'malformed email' => ['email', 'not-an-email', 'email'],
    'email too long' => ['email', str_repeat('a', 90).'@example.org', 'max'],
]);

test('an email already used, whatever its case, is rejected', function () {
    createUser(['email' => 'tom.bernard@example.org']);

    registerForm(['email' => 'TOM.Bernard@example.org'])
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('a logged in user is sent away from the register page', function () {
    $this->actingAs(createUser())
        ->get(route('register'))
        ->assertRedirect(route('clubs.index'));
});
