<?php

use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed();
});

test('logging out ends the session and goes back to the home page', function () {
    $this->actingAs(createUser());
    $token = session()->token();

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('home'));
    expect(Auth::check())->toBeFalse()
        ->and(session()->token())->not->toBe($token);
});

test('logging out requires a post request', function () {
    $this->actingAs(createUser())
        ->get('/logout')
        ->assertMethodNotAllowed();
});

test('a guest cannot log out', function () {
    $this->post(route('logout'))->assertRedirect(route('login'));
});
