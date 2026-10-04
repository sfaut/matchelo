<?php

use App\Models\User;
use Database\Seeders\DemoSeeder;

/**
 * Indique si les animateurs de démonstration existent.
 */
function demoDataExists(): bool
{
    return User::where('email', 'like', 'animateur%@matchelo.test')->exists();
}

test('the database seeder skips the demo data outside the local environment', function () {
    $this->seed();

    expect(demoDataExists())->toBeFalse()
        ->and(User::where('is_administrator', true)->count())->toBe(1);
});

test('the database seeder adds the demo data in the local environment', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->seed();

    expect(User::where('email', 'like', 'animateur%@matchelo.test')->count())->toBe(DemoSeeder::ANIMATORS);
});
