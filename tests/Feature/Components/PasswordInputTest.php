<?php

test('the password input starts hidden with a show toggle bound to the field', function () {
    $view = $this->blade('<x-password-input id="secret" autocomplete="current-password" />');

    $view->assertSee('id="secret"', false);
    $view->assertSee('type="password"', false);
    $view->assertSee('autocomplete="current-password"', false);
    $view->assertSee('aria-controls="secret"', false);
    $view->assertSee('aria-pressed="false"', false);
    $view->assertSee("x-bind:type=\"visible ? 'text' : 'password'\"", false);
    $view->assertSee(__('components.password_input.show'));
    $view->assertSee(__('components.password_input.hide'));
});
