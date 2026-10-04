<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/register', 'pages::auth.register')->name('register');

    // Provisoire : parcours de réinitialisation à venir
    Route::view('/forgot-password', 'placeholder')->name('password.request');
});

// Provisoires : remplacées aux étapes suivantes
Route::view('/club', 'placeholder')->name('club.dashboard');
Route::view('/play', 'placeholder')->name('player.home');

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
