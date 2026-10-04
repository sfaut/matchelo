<?php

/**
 * Lit un fichier du projet, commentaires CSS et Blade retirés.
 *
 * @param  string  $relative_path
 *                                 chemin relatif à la racine du projet
 */
function projectSource(string $relative_path): string
{
    $source = file_get_contents(dirname(__DIR__, 2)."/{$relative_path}");

    return preg_replace(['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '', $source);
}

/**
 * Liste les vues Blade du projet, chemins relatifs à la racine.
 *
 * @return list<string>
 */
function bladeViews(): array
{
    $root = dirname(__DIR__, 2);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/resources/views"));
    $views = [];

    foreach ($files as $file) {
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            $views[] = str_replace('\\', '/', substr($file->getPathname(), mb_strlen($root) + 1));
        }
    }

    sort($views);

    return $views;
}

/**
 * Liste les variables --mx-* déclarées dans un source CSS.
 *
 * @return list<string>
 */
function declaredTokens(string $css): array
{
    preg_match_all('/(--mx-[\w-]+)\s*:/', $css, $matches);

    return array_values(array_unique($matches[1]));
}

/**
 * Liste les variables --mx-* lues (var(), --spacing()…) dans un source.
 *
 * @return list<string>
 */
function usedTokens(string $source): array
{
    preg_match_all('/var\((--mx-[\w-]+)/', $source, $matches);

    return array_values(array_unique($matches[1]));
}

test('every token read by the app is declared by each theme or by the engine', function () {
    $engine = projectSource('resources/css/matchelo.css');
    $sources = [$engine, projectSource('resources/css/app.css'), ...array_map(projectSource(...), bladeViews())];
    $used_tokens = array_unique(array_merge(...array_map(usedTokens(...), $sources)));

    foreach (glob(dirname(__DIR__, 2).'/resources/css/themes/*.css') as $theme_file) {
        $declared_tokens = [...declaredTokens(file_get_contents($theme_file)), ...declaredTokens($engine)];

        expect(array_values(array_diff($used_tokens, $declared_tokens)))->toBe([], basename($theme_file));
    }
});

test('the engine holds no hard-coded design value', function () {
    $engine = projectSource('resources/css/matchelo.css');

    expect($engine)
        ->not->toMatch('/#[0-9a-fA-F]{3,8}\b/')
        ->not->toMatch('/\b\d*\.?\d+(px|rem|em|ms|s)\b/');
});

test('views hold no hard-coded size or color', function (string $view) {
    $source = projectSource($view);

    expect($source)
        ->not->toMatch('/\[[^\]\s]*\d(px|rem)[^\]\s]*\]/')
        ->not->toMatch('/#(?:[0-9a-fA-F]{3}){1,2}\b/')
        ->not->toMatch('/style="[^"]*\d(px|rem)/');
})->with(bladeViews());

test('tailwind default scales are replaced by the theme', function () {
    $app_css = projectSource('resources/css/app.css');

    foreach (['color', 'font', 'font-weight', 'text', 'leading', 'tracking', 'radius', 'shadow', 'container'] as $namespace) {
        expect($app_css)->toContain("--{$namespace}-*: initial;");
    }

    expect($app_css)->toContain('--spacing: var(--mx-spacing);');
});
