<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Provisoires : requises par <x-mode-switch>, remplacées aux étapes suivantes
Route::view('/club', 'placeholder')->name('club.dashboard');
Route::view('/play', 'placeholder')->name('player.home');

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
