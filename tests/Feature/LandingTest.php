<?php

test('the landing page is the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Pointez, jouez, progressez.');
});

test('the landing page shows the student, teacher and how-it-works sections', function () {
    $response = $this->get(route('home'));

    $response->assertSeeInOrder([
        'id="features"',
        'Chaque partie fait bouger ton classement.',
        'id="teachers"',
        'Le prof anime, Matchelo organise.',
        'id="how-it-works"',
        'Prêt en une séance.',
        'Prêt pour la prochaine séance ?',
    ], false);
});

test('the landing page links to login and registration', function () {
    $response = $this->get(route('home'));

    $response->assertSee('href="'.route('login').'"', false);
    $response->assertSee('href="'.route('register').'"', false);
});

test('the landing page offers the scheme toggle', function () {
    $response = $this->get(route('home'));

    $response->assertSee('x-on:click="$store.scheme.toggle()"', false);
});

test('the landing page never draws pieces with unicode glyphs', function () {
    $response = $this->get(route('home'));

    foreach (['♔', '♕', '♖', '♗', '♘', '♙', '♚', '♛', '♜', '♝', '♞', '♟'] as $glyph) {
        $response->assertDontSee($glyph);
    }
});

test('the header navigation targets the page sections', function (string $anchor) {
    $response = $this->get(route('home'));

    $response->assertSee("href=\"#{$anchor}\"", false);
    $response->assertSee("id=\"{$anchor}\"", false);
})->with(['features', 'teachers', 'how-it-works']);
