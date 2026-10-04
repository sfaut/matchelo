<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/register', 'pages::auth.register')->name('register');

    // Provisoire : parcours de réinitialisation à venir
    Route::view('/forgot-password', 'placeholder')->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/clubs', 'pages::clubs.index')->name('clubs.index');
    Route::livewire('/clubs/create', 'pages::clubs.create')->name('clubs.create');
    Route::livewire('/clubs/{club}/edit', 'pages::clubs.edit')->name('clubs.edit');

    // Accueil du mode « Jouer » : la liste des clubs
    Route::redirect('/play', '/clubs')->name('player.home');

    Route::post('/logout', LogoutController::class)->name('logout');

    // Provisoires : remplacées aux étapes suivantes
    Route::view('/club', 'placeholder')->name('club.dashboard');
});

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
