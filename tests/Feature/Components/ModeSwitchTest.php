<?php

test('the mode switch links to the club dashboard and the player home', function () {
    $view = $this->blade('<x-mode-switch />');

    $view->assertSeeInOrder([
        'href="'.route('club.dashboard').'"',
        'Gérer le club',
        'href="'.route('player.home').'"',
        'Jouer',
    ], false);
});

test('the mode switch marks the current mode', function (string $mode, string $current_label) {
    $view = $this->blade('<x-mode-switch :mode="$mode" />', ['mode' => $mode]);

    expect((string) $view)
        ->toMatch("#aria-current=\"true\"\\s*>{$current_label}</a>#")
        ->and(substr_count((string) $view, 'aria-current="true"'))->toBe(1);
})->with([
    'manage' => ['manage', 'Gérer le club'],
    'play' => ['play', 'Jouer'],
]);
