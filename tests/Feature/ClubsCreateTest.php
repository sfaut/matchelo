<?php

use App\Models\Club;
use App\Models\User\Role;
use App\Support\ClubLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    Storage::fake('public');
    $this->user = createUser();
    $this->actingAs($this->user);
});

test('guests are sent to the login page', function () {
    auth()->logout();

    $this->get(route('clubs.create'))->assertRedirect(route('login'));
});

test('the create club page renders its fields', function () {
    $response = $this->get(route('clubs.create'));

    $response->assertOk();
    $response->assertSee(__('clubs.create.title'));
    $response->assertSeeInOrder([
        __('clubs.form.fields.logo'),
        __('clubs.form.fields.name'),
        __('clubs.form.fields.city'),
        __('clubs.form.fields.description'),
        __('clubs.create.submit'),
    ]);
});

test('creating a club makes the creator its president and goes back to the clubs with a toast', function () {
    Livewire::test('pages::clubs.create')
        ->set('form.name', '  Échiquier Nord ')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('clubs.index');

    $club = Club::where('name', 'Échiquier Nord')->firstOrFail();

    expect($club->code)->toMatch('/^EN-\d{4}$/')
        ->and($club->city)->toBe('')
        ->and($club->description)->toBe('')
        ->and($club->logo_path)->toBe('')
        ->and($club->logoUrl())->toBeNull()
        ->and($club->creator->is($this->user))->toBeTrue()
        ->and($club->members->first()->membership->role_id)->toBe(Role::idFor(Role::PRESIDENT))
        ->and($this->user->presides($club))->toBeTrue()
        ->and(session('toast'))->toBe(__('clubs.create.created', ['name' => 'Échiquier Nord']));
});

test('the optional city and description are stored trimmed', function () {
    Livewire::test('pages::clubs.create')
        ->set('form.name', 'Échecs · Collège Jean-Moulin')
        ->set('form.city', ' Lyon ')
        ->set('form.description', ' Jeudi 12h30 · CDI ')
        ->call('create')
        ->assertHasNoErrors();

    $club = Club::where('name', 'Échecs · Collège Jean-Moulin')->firstOrFail();

    expect($club->city)->toBe('Lyon')
        ->and($club->description)->toBe('Jeudi 12h30 · CDI');
});

test('the logo is cropped to a square webp on the public disk', function () {
    Livewire::test('pages::clubs.create')
        ->set('form.name', 'Échiquier Nord')
        ->set('form.logo', UploadedFile::fake()->image('logo.png', 400, 300))
        ->call('create')
        ->assertHasNoErrors();

    $club = Club::where('name', 'Échiquier Nord')->firstOrFail();
    $size = getimagesizefromstring(Storage::disk('public')->get($club->logo_path));

    expect($club->logo_path)->toMatch("#^clubs/logos/{$club->id}-[a-z0-9]{8}\.webp$#")
        ->and($size[0])->toBe(ClubLogo::SIZE)
        ->and($size[1])->toBe(ClubLogo::SIZE)
        ->and($size['mime'])->toBe('image/webp')
        ->and($club->logoUrl())->toBe(Storage::disk('public')->url($club->logo_path));
});

test('invalid values are rejected', function (string $field, mixed $value, string $rule) {
    Livewire::test('pages::clubs.create')
        ->set('form.name', 'Échiquier Nord')
        ->set("form.{$field}", $value instanceof Closure ? $value() : $value)
        ->call('create')
        ->assertHasErrors(["form.{$field}" => $rule]);

    expect(Club::count())->toBe(0);
})->with([
    'missing name' => ['name', '', 'required'],
    'name too short' => ['name', 'E', 'min'],
    'name too long' => ['name', str_repeat('a', 51), 'max'],
    'city too long' => ['city', str_repeat('a', 51), 'max'],
    'description too long' => ['description', str_repeat('a', 301), 'max'],
    'logo not an image' => ['logo', fn () => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'), 'image'],
    'logo in another format' => ['logo', fn () => UploadedFile::fake()->image('logo.gif'), 'mimes'],
    'logo too heavy' => ['logo', fn () => UploadedFile::fake()->image('logo.png')->size(2049), 'max'],
]);

test('the clubs page shows the creation toast and the club logo', function () {
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $this->user->id, 'logo_path' => 'clubs/logos/1.webp']);
    $club->members()->attach($this->user, ['role_id' => Role::idFor(Role::ANIMATOR)]);
    session()->flash('toast', 'Échiquier Nord créé');

    $response = $this->get(route('clubs.index'));

    $response->assertSee('Échiquier Nord créé');
    $response->assertSee('src="'.$club->logoUrl().'"', false);
});

test('a club code takes two initials of the name and four digits', function (string $name, string $prefix) {
    expect(Club::generateCode($name))->toMatch("/^{$prefix}-\\d{4}$/");
})->with([
    'two words' => ['Échiquier Nord', 'EN'],
    'separators ignored' => ['Échecs · Collège Jean-Moulin', 'EC'],
    'single word padded' => ['Roque', 'RX'],
]);

test('a club code already taken is drawn again', function () {
    Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1111', 'user_id' => $this->user->id]);
    $draws = [1111, 2222];

    // Closure par référence : une fonction fléchée copierait $draws et tirerait toujours 1111
    expect(Club::generateCode('Échiquier Nord', function () use (&$draws): int {
        return array_shift($draws);
    }))->toBe('EN-2222');
});
