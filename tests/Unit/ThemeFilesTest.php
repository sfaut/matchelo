<?php

/**
 * Liste les fichiers de thème, indexés par nom de thème pour les jeux de données Pest.
 *
 * @return array<string, array{string}>
 */
function themeFiles(): array
{
    $theme_files = glob(dirname(__DIR__, 2).'/resources/css/themes/*.css') ?: [];

    return collect($theme_files)
        ->mapWithKeys(fn (string $theme_file): array => [basename($theme_file, '.css') => [$theme_file]])
        ->all();
}

test('at least one theme file exists', function () {
    expect(themeFiles())->not->toBeEmpty();
});

test('each theme color is declared once with light-dark()', function (string $theme_file) {
    $css = file_get_contents($theme_file);

    foreach (['bg', 'surface', 'surface-2', 'ink', 'muted', 'accent', 'on-accent', 'highlight', 'error'] as $token) {
        expect(substr_count($css, "--mx-color-{$token}:"))->toBe(1)
            ->and($css)->toMatch("/--mx-color-{$token}: light-dark\(#[0-9a-f]{6}, #[0-9a-f]{6}\);/");
    }
})->with(themeFiles());

test('a theme file only declares custom properties on the root', function (string $theme_file) {
    $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents($theme_file));

    preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);

    expect($rules)->not->toBeEmpty();

    foreach ($rules as [, $selector, $body]) {
        expect(trim($selector))->toBe(':root');

        foreach (array_filter(array_map(trim(...), explode(';', $body))) as $declaration) {
            expect($declaration)->toStartWith('--mx-');
        }
    }
})->with(themeFiles());
