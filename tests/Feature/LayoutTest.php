<?php

test('the layout defaults to the light scheme', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSee('data-scheme="light">', false);
});

test('the layout applies the stored scheme inline in the head', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSeeInOrder([
        '<head>',
        "localStorage.getItem('matchelo-scheme')",
        '</head>',
    ], false);
});

test('the layout suffixes the page title with the app name', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSee('<title>À venir · Matchelo</title>', false);
});

test('the layout loads Livewire and Alpine from the Vite bundle only', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSee('window.livewireScriptConfig', false);
    expect($response->getContent())->not->toMatch('#<script[^>]+src="[^"]*livewire(\.min)?\.js#');
});
