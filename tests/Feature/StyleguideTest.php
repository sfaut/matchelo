<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

test('the styleguide responds successfully', function () {
    $response = $this->get('/styleguide');

    $response->assertOk();
});

test('the styleguide shows the twelve piece files', function () {
    $response = $this->get('/styleguide');

    foreach (['white', 'black'] as $side) {
        foreach (['king', 'queen', 'rook', 'bishop', 'knight', 'pawn'] as $type) {
            $response->assertSee("images/pieces/{$side}-{$type}.svg", false);
        }
    }
});

test('the styleguide is not exposed in production', function () {
    app()->detectEnvironment(fn () => 'production');
    Route::setRoutes(new RouteCollection);
    require base_path('routes/web.php');

    $response = $this->get('/styleguide');

    $response->assertNotFound();
});
