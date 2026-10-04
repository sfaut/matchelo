<?php

test('the theme toggle is a toggle button bound to the theme store', function () {
    $view = $this->blade('<x-theme-toggle />');

    $view->assertSee('<button type="button" class="mx-theme-toggle"', false);
    $view->assertSee('aria-label="Thème sombre"', false);
    $view->assertSee('x-bind:aria-pressed="$store.theme.isDark"', false);
    $view->assertSee('x-on:click="$store.theme.toggle()"', false);
});

test('the theme toggle starts unpressed before Alpine boots', function () {
    $view = $this->blade('<x-theme-toggle />');

    $view->assertSee('aria-pressed="false"', false);
});
