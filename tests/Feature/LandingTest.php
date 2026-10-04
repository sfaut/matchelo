<?php

test('the landing page is the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Pointez, jouez, progressez.');
});

test('the landing page shows the student, teacher and how-it-works sections', function () {
    $response = $this->get(route('home'));

    $response->assertSeeInOrder([
        'id="fonctionnalites"',
        'Chaque partie fait bouger ton classement.',
        'id="profs"',
        'Le prof anime, Matchelo organise.',
        'id="comment"',
        'Prêt en une séance.',
        'Prêt pour la prochaine séance ?',
    ], false);
});

test('the landing page links to login and registration', function () {
    $response = $this->get(route('home'));

    $response->assertSee('href="'.route('login').'"', false);
    $response->assertSee('href="'.route('register').'"', false);
});

test('the landing page offers the theme toggle', function () {
    $response = $this->get(route('home'));

    $response->assertSee('data-theme-toggle', false);
});

test('the landing page never draws pieces with unicode glyphs', function () {
    $response = $this->get(route('home'));

    foreach (['♔', '♕', '♖', '♗', '♘', '♙', '♚', '♛', '♜', '♝', '♞', '♟'] as $glyph) {
        $response->assertDontSee($glyph);
    }
});
