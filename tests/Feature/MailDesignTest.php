<?php

use App\Notifications\AccountCreated;

/**
 * Rend en HTML l'e-mail de création de compte d'un utilisateur de test.
 */
function renderAccountCreatedMail(): string
{
    $user = createUser();

    return (string) (new AccountCreated('Secret12345'))->toMail($user)->render();
}

beforeEach(function () {
    $this->seed();
});

test('emails use the matchelo markdown theme', function () {
    expect(config('mail.markdown.theme'))->toBe('matchelo')
        ->and(file_exists(resource_path('views/vendor/mail/html/themes/matchelo.css')))->toBeTrue();
});

test('the email header shows the png logo and the wordmark', function () {
    $html = renderAccountCreatedMail();

    expect($html)
        ->toContain('src="'.asset('images/mail/logo.png').'"')
        ->toContain('>matchelo</span>')
        ->and(file_exists(public_path('images/mail/logo.png')))->toBeTrue();
});

test('the email follows the design: square corners, 2px ink stroke, offset shadow', function () {
    $html = renderAccountCreatedMail();

    expect($html)
        ->toContain('border: 2px solid #0c0c0c')
        ->toContain('box-shadow: 2px 2px 0 #ffffff, 4px 4px 0 #0c0c0c')
        ->toContain('background-color: #0c0c0c')
        ->toContain("font-family: 'Space Grotesk'")
        ->not->toMatch('/border-radius: [1-9]/');
});

test('the email footer is translated', function () {
    app()->setLocale('fr');

    expect(renderAccountCreatedMail())->toContain('Tous droits réservés.');
});
