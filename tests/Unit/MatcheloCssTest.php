<?php

/**
 * Lit matchelo.css, commentaires retirés.
 */
function matcheloCss(): string
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/matchelo.css');

    return preg_replace('#/\*.*?\*/#s', '', $css);
}

/**
 * Donne le corps d'un bloc @utility de matchelo.css, accolades imbriquées comprises.
 *
 * @throws RuntimeException si l'utilitaire n'est pas déclaré
 */
function matcheloUtility(string $name): string
{
    $css = matcheloCss();

    if (! preg_match('/@utility '.preg_quote($name, '/').' \{/', $css, $match, PREG_OFFSET_CAPTURE)) {
        throw new RuntimeException("Utilitaire {$name} absent de matchelo.css");
    }

    // Travail en octets : PREG_OFFSET_CAPTURE donne des positions en octets, et seules
    // les accolades ASCII comptent (jamais confondues avec un octet de séquence UTF-8)
    $start = $match[0][1] + strlen($match[0][0]);
    $depth = 1;

    for ($index = $start; $depth > 0; $index++) {
        $depth += match ($css[$index]) {
            '{' => 1,
            '}' => -1,
            default => 0,
        };
    }

    return substr($css, $start, $index - $start - 1);
}

/**
 * Liste les blocs imbriqués (états, descendants) d'un utilitaire.
 *
 * @return list<array{selector: string, body: string}>
 */
function matcheloNestedRules(string $name): array
{
    preg_match_all('/([^{};]+)\{([^{}]*)\}/', matcheloUtility($name), $matches, PREG_SET_ORDER);

    return array_map(fn (array $match): array => [
        'selector' => trim($match[1]),
        'body' => $match[2],
    ], $matches);
}

test('components are declared as utilities so they accept variants', function () {
    expect(matcheloCss())->not->toContain('@layer components');

    foreach (['mx-btn', 'mx-btn--primary', 'mx-btn--sm', 'mx-btn--link', 'mx-card', 'mx-card--highlight',
        'mx-card--on-accent', 'mx-label', 'mx-input', 'mx-error', 'mx-chip', 'mx-seg', 'mx-seg__item',
        'mx-scheme-toggle', 'mx-scheme-toggle__sun', 'mx-scheme-toggle__moon', 'mx-logo', 'mx-logo__mark',
        'mx-logo__word', 'mx-logo--inverse', 'mx-logo--sm', 'mx-logo--lg', 'mx-inverse', 'mx-toast',
        'mx-border', 'mx-border-t', 'mx-border-b', 'mx-border-y'] as $name) {
        expect(matcheloUtility($name))->not->toBeEmpty();
    }
});

test('no button modifier redeclares the hover shadow', function () {
    preg_match_all('/@utility (mx-btn--[\w-]+) \{/', matcheloCss(), $matches);

    foreach ($matches[1] as $name) {
        $overriding_rules = array_filter(
            matcheloNestedRules($name),
            fn (array $rule): bool => str_contains($rule['selector'], ':hover')
                && ! str_contains($rule['selector'], ':active')
                && str_contains($rule['body'], 'box-shadow'),
        );

        expect($overriding_rules)->toBeEmpty();
    }
});

test('the pressed button drops its shadow', function () {
    $active_rule = collect(matcheloNestedRules('mx-btn'))->firstWhere('selector', '&:active');

    expect($active_rule['body'])
        ->toContain('transform: translate(var(--mx-press-shift), var(--mx-press-shift))')
        ->toContain('box-shadow: none');
});

test('the logo size modifiers switch to the theme logo sizes', function () {
    expect(matcheloUtility('mx-logo__mark'))->toContain('width: var(--mx-logo-mark-size, var(--mx-logo-mark))')
        ->and(matcheloUtility('mx-logo__word'))->toContain('font-size: var(--mx-logo-word-size, var(--mx-logo-word))')
        ->and(matcheloUtility('mx-logo--sm'))->toContain('--mx-logo-mark-size: var(--mx-logo-mark-sm)')
        ->and(matcheloUtility('mx-logo--lg'))->toContain('--mx-logo-mark-size: var(--mx-logo-mark-lg)');
});

test('the stroke utilities use detailed properties so border colors can override them', function () {
    expect(matcheloUtility('mx-border'))
        ->toContain('border-width: var(--mx-stroke)')
        ->toContain('border-color: var(--mx-color-ink)')
        ->not->toMatch('/\bborder:/');
});

test('the dark variant follows the data-scheme attribute', function () {
    expect(file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css'))->toContain('@custom-variant dark (&:where([data-scheme=dark], [data-scheme=dark] *));');
});

test('dark styles go through the dark variant', function () {
    expect(matcheloCss())->not->toContain("[data-scheme='dark'] .");
});

test('the data-scheme attribute drives the color scheme', function () {
    expect(matcheloCss())
        ->toMatch('/:root \{\s*color-scheme: light;/')
        ->toMatch("/\[data-scheme='dark'\] \{\s*color-scheme: dark;\s*\}/");
});
