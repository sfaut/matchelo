<?php

use App\Models\AttendanceStatus;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\EventType;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed();
});

test('the seeders fill the reference tables', function () {
    expect(Role::pluck('code')->all())->toEqualCanonicalizing(['PRESIDENT', 'ANIMATOR', 'PLAYER'])
        ->and(Role::where('description', '')->exists())->toBeFalse()
        ->and(EventType::pluck('code')->all())->toEqualCanonicalizing(['COURSE', 'TOURNAMENT', 'OTHER'])
        ->and(EventStatus::pluck('code')->all())->toEqualCanonicalizing(['DRAFT', 'SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'FINALIZED', 'CANCELLED'])
        ->and(AttendanceStatus::pluck('code')->all())->toEqualCanonicalizing(['EXPECTED', 'PRESENT', 'ABSENT', 'EXCUSED', 'LATE']);
});

test('the statuses list in their display order, set by tens', function () {
    expect(EventStatus::ordered()->pluck('code')->all())->toBe(['DRAFT', 'SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'FINALIZED', 'CANCELLED'])
        ->and(AttendanceStatus::ordered()->pluck('code')->all())->toBe(['EXPECTED', 'PRESENT', 'ABSENT', 'EXCUSED', 'LATE'])
        ->and(EventStatus::pluck('display_order')->concat(AttendanceStatus::pluck('display_order'))->every(fn (int $order): bool => $order % 10 === 0))->toBeTrue();

    AttendanceStatus::where('code', AttendanceStatus::LATE)->update(['display_order' => 25]);

    expect(AttendanceStatus::ordered()->pluck('code')->all())->toBe(['EXPECTED', 'PRESENT', 'LATE', 'ABSENT', 'EXCUSED']);
});

test('a user is not an administrator by default', function () {
    $user = User::create([
        'last_name' => 'Kaci',
        'first_name' => 'Inès',
        'email' => 'ines.kaci@example.org',
        'password_hash' => 'secret-password',
    ])->refresh();

    expect($user->is_administrator)->toBeFalse();
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
    $club = Club::create(['name' => 'Échecs · Collège Jean-Moulin', 'code' => 'JM-4821', 'user_id' => $creator->id]);

    $club->members()->attach($creator, ['role_id' => Role::where('code', 'ANIMATOR')->value('id')]);
    $club->members()->attach($player, ['role_id' => Role::where('code', 'PLAYER')->value('id')]);

    expect($club->creator->is($creator))->toBeTrue()
        ->and($creator->createdClubs->pluck('id')->all())->toBe([$club->id])
        ->and($club->members->pluck('id')->all())->toEqualCanonicalizing([$creator->id, $player->id])
        ->and($player->clubs->first()->pivot->role_id)->toBe(Role::where('code', 'PLAYER')->value('id'));
});

test('a club section belongs to its club and gathers players', function () {
    $creator = createUser();
    $player = createUser(['email' => 'tom.bernard@example.org', 'first_name' => 'Tom', 'last_name' => 'Bernard']);
    $club = Club::create(['name' => 'Échecs · Collège Jean-Moulin', 'code' => 'JM-4821', 'user_id' => $creator->id]);

    $general_section = $club->createDefaultSection();
    $youth_section = $club->sections()->create(['name' => 'Section jeunes']);
    $general_section->players()->attach($player);
    $youth_section->players()->attach($player);

    expect($general_section->name)->toBe(__('clubs.sections.default_name'))
        ->and($general_section->club->is($club))->toBeTrue()
        ->and($club->sections->pluck('name')->all())->toEqualCanonicalizing([__('clubs.sections.default_name'), 'Section jeunes'])
        ->and($player->sections->pluck('id')->all())->toEqualCanonicalizing([$general_section->id, $youth_section->id])
        ->and($creator->sections)->toBeEmpty();
});

test('the general section is named in the current language', function () {
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => createUser()->id]);
    $other_club = Club::create(['name' => 'Club voisin', 'code' => 'CV-1234', 'user_id' => createUser(['email' => 'tom.bernard@example.org'])->id]);

    app()->setLocale('en');
    expect($club->createDefaultSection()->name)->toBe('General section');

    app()->setLocale('fr');
    expect($other_club->createDefaultSection()->name)->toBe('Section générale');
});

test('a section name is unique within its club only', function () {
    $creator = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $creator->id]);
    $other_club = Club::create(['name' => 'Club voisin', 'code' => 'CV-1234', 'user_id' => $creator->id]);
    $club->createDefaultSection();

    expect($other_club->createDefaultSection()->name)->toBe(__('clubs.sections.default_name'))
        ->and(fn () => $club->createDefaultSection())->toThrow(QueryException::class);
});

test('deleting a section frees its players, and deleting a club deletes its sections', function () {
    $creator = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $creator->id]);
    $section = $club->createDefaultSection();
    $section->players()->attach($creator);

    $section->delete();

    expect(DB::table('section_user')->count())->toBe(0)
        ->and($creator->fresh())->not->toBeNull();

    $club->createDefaultSection();
    $club->delete();

    expect(DB::table('sections')->count())->toBe(0);
});

test('a section member holds a role, the player role when added as a player', function () {
    $creator = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $creator->id]);
    $section = $club->createDefaultSection();
    $section->players()->attach($creator);

    expect($creator->sections->first()->pivot->role_id)->toBe(Role::idFor(Role::PLAYER));
});

test('deleting a user deletes their section memberships', function () {
    $player = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => createUser(['email' => 'tom@example.org'])->id]);
    $club->createDefaultSection()->players()->attach($player);

    $player->delete();

    expect(DB::table('section_user')->count())->toBe(0);
});

test('a user who created a club cannot be deleted', function () {
    $creator = createUser();
    Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $creator->id]);

    expect(fn () => $creator->delete())->toThrow(QueryException::class);
});

test('a reference id change cascades to the rows using it', function () {
    $user = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $user->id]);
    $club->members()->attach($user, ['role_id' => Role::idFor(Role::PLAYER)]);

    DB::table('roles')->where('code', Role::PLAYER)->update(['id' => 40]);

    expect($club->members()->first()->pivot->role_id)->toBe(40);
});

test('a role id is found by its code', function () {
    expect(Role::idFor(Role::ANIMATOR))->toBe(Role::where('code', 'ANIMATOR')->value('id'))
        ->and(Role::idFor('UNKNOWN'))->toBeNull();
});

test('role ids are found by their codes', function () {
    expect(Role::idsFor(Role::MANAGERS))->toEqualCanonicalizing([Role::idFor(Role::PRESIDENT), Role::idFor(Role::ANIMATOR)])
        ->and(Role::idsFor(['UNKNOWN']))->toBe([]);
});

test('a user manages a club once they preside or animate it', function () {
    $user = createUser();
    $club = Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $user->id]);

    $club->members()->attach($user, ['role_id' => Role::idFor(Role::PLAYER)]);
    expect($user->isManager())->toBeFalse()
        ->and($user->managedClubs()->count())->toBe(0)
        ->and($user->presides($club))->toBeFalse();

    $club->members()->updateExistingPivot($user->id, ['role_id' => Role::idFor(Role::ANIMATOR)]);
    expect($user->isManager())->toBeTrue()
        ->and($user->managedClubs()->count())->toBe(1)
        ->and($user->presides($club))->toBeFalse();

    $club->members()->updateExistingPivot($user->id, ['role_id' => Role::idFor(Role::PRESIDENT)]);
    expect($user->isManager())->toBeTrue()
        ->and($user->managedClubs()->count())->toBe(1)
        ->and($user->presides($club))->toBeTrue();
});

test('the full name is the first name then the last name', function () {
    expect(createUser()->fullName())->toBe('Léa Martin');
});

test('a club code is unique', function () {
    $creator = createUser();
    Club::create(['name' => 'Échiquier Nord', 'code' => 'EN-1177', 'user_id' => $creator->id]);

    expect(fn () => Club::create(['name' => 'Échiquier Nouveau', 'code' => 'EN-1177', 'user_id' => $creator->id]))
        ->toThrow(QueryException::class);
});

test('a user starts at 1200 ELO', function () {
    expect(createUser()->refresh()->elo_rating)->toBe(1200);
});

test('a player without email has no account and cannot log in', function () {
    $player = User::create(['last_name' => 'Diallo', 'first_name' => 'Nathan'])->refresh();

    expect($player->email)->toBeNull()
        ->and($player->password_hash)->toBe('')
        ->and($player->hasAccount())->toBeFalse()
        ->and(createUser()->hasAccount())->toBeTrue();
});

test('the model timestamps keep their microseconds', function (Closure $create_model) {
    $this->travelTo(Carbon::parse('2026-10-09 10:15:30.123456'));
    $model = $create_model();

    $this->travelTo(Carbon::parse('2026-10-09 10:15:31.654321'));
    $model->touch();

    expect(DB::table($model->getTable())->where('id', $model->id)->first())
        ->created_at->toBe('2026-10-09 10:15:30.123456')
        ->updated_at->toBe('2026-10-09 10:15:31.654321');
})->with([
    'role' => fn (): Role => Role::create(['code' => 'COACH', 'name' => 'Coach']),
    'user' => fn (): User => createUser(),
    'club' => fn (): Club => Club::factory()->create(),
    'club session' => fn (): Event => Event::factory()->create(),
]);

test('a club session is scheduled to the second', function () {
    $session = Event::factory()->create(['scheduled_at' => Carbon::parse('2026-10-09 18:30:15.987654')]);

    expect(DB::table('events')->where('id', $session->id)->value('scheduled_at'))->toBe('2026-10-09 18:30:15')
        ->and($session->refresh()->scheduled_at->format('Y-m-d H:i:s.u'))->toBe('2026-10-09 18:30:15.000000');
});
