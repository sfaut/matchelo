<?php

dataset('localized pages', [
    'landing' => fn (): string => route('home'),
    'placeholder' => fn (): string => route('password.request'),
    'styleguide' => fn (): string => route('styleguide'),
]);

test('a page shows no untranslated key in any supported language', function (string $url) {
    foreach (config('matchelo.locales') as $locale) {
        $response = $this->withHeader('Accept-Language', $locale)->get($url);

        $response->assertOk();

        foreach (['landing.', 'components.', 'placeholder.', 'styleguide.'] as $key_prefix) {
            $response->assertDontSee($key_prefix, false);
        }
    }
})->with('localized pages');

test('the landing page is shown in french to a french browser', function () {
    $response = $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get(route('home'));

    $response->assertSee('Pointez, jouez, progressez.');
    $response->assertSee('Créer mon club');
});

test('the landing page is shown in english to any other browser', function () {
    $response = $this->withHeader('Accept-Language', 'ja')->get(route('home'));

    $response->assertSee('Check in, play, improve.');
    $response->assertSee('Create my club');
});

test('numbers and ordinals follow the page language', function (string $locale, string $rating, string $rank) {
    $response = $this->withHeader('Accept-Language', $locale)->get(route('home'));

    $response->assertSee($rating, false);
    $response->assertSee($rank, false);
})->with([
    'french' => ['fr', "1\u{202F}248", 'Léa Martin · 5e · 12 parties'],
    'english' => ['en', '1,248', 'Léa Martin · 5th · 12 games'],
]);
