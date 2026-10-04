<?php

test('the locale is the preferred browser language among the supported ones', function (string $accept_language, string $expected_locale) {
    $response = $this->withHeader('Accept-Language', $accept_language)->get(route('home'));

    $response->assertSee("<html lang=\"{$expected_locale}\"", false);
})->with([
    'french' => ['fr', 'fr'],
    'regional french' => ['fr-FR,fr;q=0.9', 'fr'],
    'english' => ['en-GB,en;q=0.8', 'en'],
    'second choice supported' => ['de-DE,de;q=0.9,fr;q=0.5', 'fr'],
]);

test('an unsupported browser language falls back to english', function () {
    $response = $this->withHeader('Accept-Language', 'ja-JP,ja;q=0.9')->get(route('home'));

    $response->assertSee('<html lang="en"', false);
});

test('a request without language preference falls back to english', function () {
    $response = $this->get(route('home'));

    $response->assertSee('<html lang="en"', false);
});

test('the response varies on the accept-language header', function () {
    $response = $this->get(route('home'));

    expect($response->headers->get('Vary'))->toContain('Accept-Language');
});
