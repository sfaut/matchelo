<?php

test('provisional routes render the placeholder screen', function (string $route_name) {
    $response = $this->get(route($route_name));

    $response->assertOk();
    $response->assertSee('Écran à venir');
})->with([
    'login',
    'register',
    'club.dashboard',
    'player.home',
]);
