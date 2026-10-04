<?php

test('the mode switch links to the management entry and the player home', function () {
    $view = $this->blade('<x-mode-switch />');

    $view->assertSeeInOrder([
        'href="'.route('manage.home').'"',
        e(__('components.mode_switch.manage')),
        'href="'.route('player.home').'"',
        e(__('components.mode_switch.play')),
    ], false);
});

test('the mode switch marks the current mode', function (string $mode, string $current_key) {
    $view = $this->blade('<x-mode-switch :mode="$mode" />', ['mode' => $mode]);
    $current_label = preg_quote(e(__($current_key)), '#');

    expect((string) $view)
        ->toMatch("#aria-current=\"true\"\\s*>{$current_label}</a>#")
        ->and(substr_count((string) $view, 'aria-current="true"'))->toBe(1);
})->with([
    'manage' => ['manage', 'components.mode_switch.manage'],
    'play' => ['play', 'components.mode_switch.play'],
]);
