<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ManagementHomeController;
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
    Route::livewire('/clubs/{club}/dashboard', 'pages::clubs.dashboard')->name('clubs.dashboard');
    Route::livewire('/clubs/{club}/attendance', 'pages::clubs.attendance')->name('clubs.attendance');
    Route::livewire('/clubs/{club}/players/add', 'pages::clubs.players.create')->name('clubs.players.create');

    // Entrée du mode « Gérer le club » : tableau de bord du premier club animé
    Route::get('/manage', ManagementHomeController::class)->name('manage.home');

    // Accueil du mode « Jouer » : la liste des clubs
    Route::redirect('/play', '/clubs')->name('player.home');

    Route::post('/logout', LogoutController::class)->name('logout');
});

if (! app()->isProduction()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}
