<?php

test('the theme toggle is a labelled button hooked to the theme script', function () {
    $view = $this->blade('<x-theme-toggle />');

    $view->assertSee('<button type="button" class="mx-theme-toggle" data-theme-toggle', false);
    $view->assertSee('aria-label="Basculer le thème clair / sombre"', false);
});
