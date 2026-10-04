<?php

test('the logo shows the king mark and the wordmark', function () {
    $view = $this->blade('<x-logo />');

    $view->assertSee('class="mx-logo"', false);
    $view->assertSee('<span class="mx-logo__word">matchelo</span>', false);
});

test('the inverse logo adds the inverse modifier', function () {
    $view = $this->blade('<x-logo inverse />');

    $view->assertSee('class="mx-logo mx-logo--inverse"', false);
});
