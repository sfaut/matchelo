<?php

test('provisional routes render the placeholder screen', function (string $route_name) {
    $response = $this->get(route($route_name));

    $response->assertOk();
    $response->assertSee(__('placeholder.heading'));
})->with([
    'password.request',
    'club.dashboard',
    'player.home',
]);
