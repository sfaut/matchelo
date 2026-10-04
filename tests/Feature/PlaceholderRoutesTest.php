<?php

test('provisional guest routes render the placeholder screen', function (string $route_name) {
    $response = $this->get(route($route_name));

    $response->assertOk();
    $response->assertSee(__('placeholder.heading'));
})->with([
    'password.request',
]);

test('provisional member routes render the placeholder screen to a logged in user', function (string $route_name) {
    $this->seed();

    $response = $this->actingAs(createUser())->get(route($route_name));

    $response->assertOk();
    $response->assertSee(__('placeholder.heading'));
})->with([
    'club.dashboard',
]);

test('provisional member routes send guests to the login page', function (string $route_name) {
    $this->get(route($route_name))->assertRedirect(route('login'));
})->with([
    'club.dashboard',
]);
