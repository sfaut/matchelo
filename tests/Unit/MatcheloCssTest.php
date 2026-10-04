<?php

/**
 * Lit les règles de matchelo.css, dans l'ordre du fichier.
 *
 * @return list<array{selector: string, body: string}>
 */
function matcheloCssRules(): array
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/matchelo.css');
    $css = preg_replace('#/\*.*?\*/#s', '', $css);

    preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER);

    return array_map(fn (array $match): array => [
        'selector' => trim($match[1]),
        'body' => $match[2],
    ], $matches);
}

test('no button hover shadow is declared after the pressed state', function () {
    $rules = matcheloCssRules();
    $selectors = array_column($rules, 'selector');
    $active_index = array_search('.mx-btn:active', $selectors, true);

    expect($active_index)->not->toBeFalse();

    $overriding_rules = array_filter(
        array_slice($rules, $active_index + 1),
        fn (array $rule): bool => str_contains($rule['selector'], '.mx-btn')
            && str_contains($rule['selector'], ':hover')
            && ! str_contains($rule['selector'], ':active')
            && str_contains($rule['body'], 'box-shadow'),
    );

    expect($overriding_rules)->toBeEmpty();
});

test('the pressed button drops its shadow', function () {
    $rules = matcheloCssRules();
    $active_rule = collect($rules)->firstWhere('selector', '.mx-btn:active');

    expect($active_rule['body'])
        ->toContain('transform: translate(4px, 4px)')
        ->toContain('box-shadow: 0 0 0');
});
