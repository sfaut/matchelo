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

/**
 * Indique si une classe figure dans un attribut class du HTML rendu.
 *
 * @param string $html
 *     page rendue
 * @param string $class
 *     classe recherchée, sans variante
 * @return bool
 */
function htmlUsesClass(string $html, string $class): bool
{
    $class_pattern = preg_quote($class, '/');

    return (bool) preg_match("/class=\"[^\"]*(?<![\w:-]){$class_pattern}(?![\w-])/", $html);
}

test('the styleguide shows every mx utility of the design system', function () {
    $html = $this->get('/styleguide')->getContent();

    preg_match_all('/@utility (mx-[\w-]+) \{/', file_get_contents(resource_path('css/matchelo.css')), $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $utility) {
        expect(htmlUsesClass($html, $utility))->toBeTrue("{$utility} absent du styleguide");
    }
});

test('the styleguide shows every theme color and text size', function () {
    $html = $this->get('/styleguide')->getContent();
    $app_css = file_get_contents(resource_path('css/app.css'));

    // Noms simples uniquement : les sous-variables (--text-h1--line-height…) sont exclues
    preg_match_all('/^\s*--color-([a-z0-9]+(?:-[a-z0-9]+)*): var\(/m', $app_css, $colors);
    preg_match_all('/^\s*--text-([a-z0-9]+(?:-[a-z0-9]+)*): var\(/m', $app_css, $text_sizes);

    expect($colors[1])->not->toBeEmpty()
        ->and($text_sizes[1])->not->toBeEmpty();

    foreach ($colors[1] as $color) {
        expect(htmlUsesClass($html, "bg-{$color}"))->toBeTrue("bg-{$color} absent du styleguide");
    }

    foreach ($text_sizes[1] as $text_size) {
        expect(htmlUsesClass($html, "text-{$text_size}"))->toBeTrue("text-{$text_size} absent du styleguide");
    }
});
