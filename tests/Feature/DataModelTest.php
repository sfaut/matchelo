<?php

use App\Models\Club;
use App\Models\User;
use App\Models\User\Civility;
use App\Models\User\Role;
use App\Models\User\Sex;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed();
});

test('the seeders fill the reference tables', function () {
    expect(Role::pluck('code')->all())->toEqualCanonicalizing(['ANIMATOR', 'PLAYER'])
        ->and(Sex::pluck('code')->all())->toEqualCanonicalizing(['UNDEFINED', 'WOMAN', 'MAN'])
        ->and(Civility::pluck('code')->all())->toEqualCanonicalizing(['UNDEFINED', 'MISS', 'MADAM', 'MISTER']);
});

test('a civility belongs to a sex', function () {
    $madam = Civility::where('code', 'MADAM')->firstOrFail();

    expect($madam->sex->code)->toBe('WOMAN')
        ->and(Sex::where('code', 'WOMAN')->firstOrFail()->civilities->pluck('code')->all())
        ->toEqualCanonicalizing(['MISS', 'MADAM']);
});

test('a user belongs to a civility', function () {
    $user = createUser();

    expect($user->civility->code)->toBe('MADAM')
        ->and($user->civility->users->pluck('id')->all())->toContain($user->id);
});

test('a user defaults to the undefined civility and is not an administrator', function () {
    $user = User::create([
        'last_name' => 'Kaci',
        'first_name' => 'Inès',
        'email' => 'ines.kaci@example.org',
        'password_hash' => 'secret-password',
    ])->refresh();

    expect($user->civility->code)->toBe('UNDEFINED')
        ->and($user->is_administrator)->toBeFalse();
});

test('the password is hashed in password_hash and used to authenticate', function () {
    $user = createUser();

    expect($user->password_hash)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password_hash))->toBeTrue()
        ->and(Auth::attempt(['email' => 'lea.martin@example.org', 'password' => 'secret-password']))->toBeTrue()
        ->and(Auth::id())->toBe($user->id);
});

test('the password hash and remember token are hidden from serialization', function () {
    expect(createUser()->toArray())->not->toHaveKeys(['password_hash', 'remember_token']);
});

test('the initials are the first letters of the first and last names', function () {
    expect(createUser(['first_name' => 'léa', 'last_name' => 'martin'])->initials())->toBe('LM');
});

test('a club has a creator and members with a role', function () {
    $creator = createUser();
    $player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $club = Club::create(['name' => 'Échecs · Collège Jean-Moulin', 'user_id' => $creator->id]);

    $club->members()->attach($creator, ['role_id' => Role::where('code', 'ANIMATOR')->value('id')]);
    $club->members()->attach($player, ['role_id' => Role::where('code', 'PLAYER')->value('id')]);

    expect($club->creator->is($creator))->toBeTrue()
        ->and($creator->createdClubs->pluck('id')->all())->toBe([$club->id])
        ->and($club->members->pluck('id')->all())->toEqualCanonicalizing([$creator->id, $player->id])
        ->and($player->clubs->first()->pivot->role_id)->toBe(Role::where('code', 'PLAYER')->value('id'));
});

test('a civility in use cannot be deleted', function () {
    $user = createUser();

    expect(fn () => $user->civility->delete())->toThrow(QueryException::class);
});

test('a user who created a club cannot be deleted', function () {
    $creator = createUser();
    Club::create(['name' => 'Échiquier Nord', 'user_id' => $creator->id]);

    expect(fn () => $creator->delete())->toThrow(QueryException::class);
});

test('a reference id change cascades to the rows using it', function () {
    $user = createUser();
    $madam = $user->civility;

    DB::table('civilities')->where('id', $madam->id)->update(['id' => 40]);

    expect($user->refresh()->civility_id)->toBe(40);
});
