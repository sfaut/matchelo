<?php

test('the layout defaults to the light theme', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSee('<html lang="fr" data-theme="light">', false);
});

test('the layout applies the stored theme inline in the head', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSeeInOrder([
        '<head>',
        "localStorage.getItem('matchelo-theme')",
        '</head>',
    ], false);
});

test('the layout suffixes the page title with the app name', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSee('<title>À venir · Matchelo</title>', false);
});
