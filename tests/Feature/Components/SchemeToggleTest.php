<?php

test('the scheme toggle is a toggle button bound to the scheme store', function () {
    $view = $this->blade('<x-scheme-toggle />');

    $view->assertSee('<button type="button" class="mx-scheme-toggle"', false);
    $view->assertSee('aria-label="Thème sombre"', false);
    $view->assertSee('x-bind:aria-pressed="$store.scheme.isDark"', false);
    $view->assertSee('x-on:click="$store.scheme.toggle()"', false);
});

test('the scheme toggle starts unpressed before Alpine boots', function () {
    $view = $this->blade('<x-scheme-toggle />');

    $view->assertSee('aria-pressed="false"', false);
});
