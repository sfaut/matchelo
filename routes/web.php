<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');

    // Provisoire : parcours de réinitialisation à venir
    Route::view('/forgot-password', 'placeholder')->name('password.request');
});

// Provisoires : remplacées aux étapes suivantes
Route::view('/register', 'placeholder')->name('register');
Route::view('/club', 'placeholder')->name('club.dashboard');
Route::view('/play', 'placeholder')->name('player.home');

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
