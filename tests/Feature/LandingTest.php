<?php

test('the landing page is the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(__('landing.hero.title'));
});

test('the landing page shows the student, teacher and how-it-works sections', function () {
    $response = $this->get(route('home'));

    $response->assertSeeInOrder([
        'id="features"',
        e(__('landing.students.title')),
        'id="teachers"',
        e(__('landing.teachers.title')),
        'id="how-it-works"',
        e(__('landing.how_it_works.title')),
        e(__('landing.cta.title')),
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
