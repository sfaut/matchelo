<?php

test('a white piece uses the theme board white with a board black outline', function () {
    $view = $this->blade('<x-piece type="king" side="white" />');

    $view->assertSee('fill-board-white stroke-board-black', false);
});

test('a black piece uses the theme board black with a board white outline', function () {
    $view = $this->blade('<x-piece type="king" side="black" />');

    $view->assertSee('fill-board-black stroke-board-white', false);
});

test('each piece type renders its own drawing', function (string $type, string $signature) {
    $view = $this->blade('<x-piece :type="$type" />', ['type' => $type]);

    $view->assertSee($signature, false);
})->with([
    'king' => ['king', 'points="11,1 13,1'],
    'queen' => ['queen', 'points="4.5,7 7,11.5'],
    'rook' => ['rook', 'points="6,2.5 9,2.5'],
    'bishop' => ['bishop', 'd="M12 4.2'],
    'knight' => ['knight', 'd="M7.5 18.5 L8.5 13'],
    'pawn' => ['pawn', 'cx="12" cy="6.5" r="3.5"'],
]);

test('a piece is an svg hidden from assistive technologies with a default size', function () {
    $view = $this->blade('<x-piece type="pawn" />');

    $view->assertSee('<svg viewBox="0 0 24 24"', false);
    $view->assertSee('aria-hidden="true"', false);
    $view->assertSee('class="block size-8 ', false);
});

test('a current color piece is filled with currentColor and has no outline', function () {
    $view = $this->blade('<x-piece type="rook" side="current" />');

    $view->assertSee('fill="currentColor"', false);
    $view->assertDontSee('stroke=', false);
});

test('a size class given by the caller replaces the default size', function () {
    $view = $this->blade('<x-piece type="rook" class="size-12" />');

    $view->assertSee('size-12"', false);
    $view->assertDontSee('size-8', false);
});
