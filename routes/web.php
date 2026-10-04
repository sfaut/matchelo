<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

// Provisoires : remplacées aux étapes suivantes
Route::view('/login', 'placeholder')->name('login');
Route::view('/register', 'placeholder')->name('register');
Route::view('/club', 'placeholder')->name('club.dashboard');
Route::view('/play', 'placeholder')->name('player.home');

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
