<?php

use App\Support\Themes;
use Illuminate\Support\Facades\File;

test('the available themes are the files of the themes directory', function () {
    expect(app(Themes::class)->available())->toContain('black-and-white');
});

test('the current theme is the configured default theme', function () {
    config()->set('matchelo.default_theme', 'black-and-white');

    expect(app(Themes::class)->current())->toBe('black-and-white');
});

test('an unknown default theme falls back to the first available theme', function () {
    config()->set('matchelo.default_theme', 'unknown-theme');
    $themes = app(Themes::class);

    expect($themes->current())->toBe($themes->available()[0]);
});

test('resolving a theme fails when the themes directory is empty', function () {
    $empty_directory = 'storage/framework/testing/empty-themes';
    File::ensureDirectoryExists(base_path($empty_directory));
    config()->set('matchelo.themes_directory', $empty_directory);

    try {
        app(Themes::class)->current();
    } finally {
        File::deleteDirectory(base_path($empty_directory));
    }
})->throws(RuntimeException::class);

test('the stylesheet path is the theme file as expected by vite', function () {
    $themes = app(Themes::class);

    expect($themes->stylesheet('black-and-white'))->toBe('resources/css/themes/black-and-white.css')
        ->and($themes->stylesheet())->toBe("resources/css/themes/{$themes->current()}.css");
});

test('the layout loads the current theme stylesheet after the app stylesheet', function () {
    $response = $this->get(route('club.dashboard'));

    $response->assertSeeInOrder(['/build/assets/app-', '/build/assets/black-and-white-'], false);
});
