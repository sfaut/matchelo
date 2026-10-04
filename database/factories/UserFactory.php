<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Utilisateurs de test : par défaut un joueur avec compte (e-mail confirmé, mot de passe
 * « password »).
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Mot de passe en clair de tous les comptes générés.
     *
     * @var string
     */
    public const string PASSWORD = 'password';

    /**
     * Hachage du mot de passe, calculé une fois pour toutes (le hachage est lent).
     */
    protected static ?string $passwordHash = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'elo' => fake()->numberBetween(900, 1600),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password_hash' => static::$passwordHash ??= Hash::make(self::PASSWORD),
        ];
    }

    /**
     * Joueur sans compte, créé par un prof : ni e-mail, ni mot de passe.
     */
    public function withoutAccount(): static
    {
        return $this
            ->state(fn (): array => [
                'email' => null,
                'email_verified_at' => null,
            ])
            // password_hash retiré (et non mis à '') : le cast hashed hacherait la chaîne vide ;
            // la valeur par défaut de la base, '', s'applique
            ->afterMaking(function (User $user): void {
                unset($user->password_hash);
            });
    }

    /**
     * Compte dont l'e-mail n'est pas encore confirmé.
     */
    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Administrateur de l'application.
     */
    public function administrator(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->forceFill(['is_administrator' => true])->save();
        });
    }
}
