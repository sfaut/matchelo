<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Page de connexion : e-mail et mot de passe, tentatives limitées par e-mail et adresse IP.
 */
new class extends Component
{
    /**
     * Nombre de tentatives échouées tolérées avant blocage temporaire.
     *
     * @var int
     */
    public const int MAX_ATTEMPTS = 5;

    /**
     * E-mail saisi.
     */
    public string $email = '';

    /**
     * Mot de passe saisi.
     */
    public string $password = '';

    /**
     * E-mail du compte tout juste créé, pour le message de confirmation ; null sinon.
     */
    #[Locked]
    public ?string $registeredEmail = null;

    /**
     * Préremplit l'e-mail après une inscription (session flash posée par la page d'inscription).
     */
    public function mount(): void
    {
        $this->registeredEmail = session('registered_email');
        $this->email = $this->registeredEmail ?? '';
    }

    /**
     * Connecte l'utilisateur, puis le renvoie vers la page demandée ou l'accueil joueur.
     *
     * @throws ValidationException si les identifiants sont faux ou les tentatives épuisées
     */
    public function login(): void
    {
        $this->email = Str::lower(trim($this->email));

        $this->validate();

        $throttle_key = Str::transliterate("{$this->email}|".request()->ip());

        if (RateLimiter::tooManyAttempts($throttle_key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($throttle_key)]),
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($throttle_key);
            $this->reset('password');

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($throttle_key);

        // Le mot de passe a été reçu par e-mail : la première connexion confirme l'adresse
        $user = Auth::user();

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        session()->regenerate();

        $this->redirectIntended(route('player.home'));
    }

    /**
     * Règles de validation du formulaire.
     *
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Libellés des champs dans les messages de validation.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'email' => __('auth_pages.fields.email'),
            'password' => __('auth_pages.fields.password'),
        ];
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('auth_pages.login.title'));
    }
};
?>

<x-auth-shell :panel-title="__('auth_pages.login.panel_title')" :panel-text="__('auth_pages.login.panel_text')">
    <form wire:submit="login" class="flex flex-col gap-4.5" novalidate>
        <h1 class="text-figure tracking-heading">{{ __('auth_pages.login.title') }}</h1>

        @if ($registeredEmail)
            <p class="mx-card mx-card--highlight text-md" role="status">
                {{ __('auth_pages.login.registered', ['email' => $registeredEmail]) }}
            </p>
        @endif

        <div class="flex flex-col gap-1.5">
            <label for="email" class="mx-label">{{ __('auth_pages.fields.email') }}</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                placeholder="{{ __('auth_pages.email_placeholder') }}"
                class="mx-input"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('email')
                <p id="email-error" class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="password" class="mx-label">{{ __('auth_pages.fields.password') }}</label>
            <x-password-input id="password" wire:model="password" autocomplete="current-password" />
            @error('password')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end text-md">
            <a href="{{ route('password.request') }}" class="underline">{{ __('auth_pages.login.forgot_password') }}</a>
        </div>

        <button type="submit" class="mx-btn mx-btn--primary w-full p-3.5 text-xl" wire:loading.attr="disabled">
            {{ __('auth_pages.login.submit') }}
        </button>

        <div class="mx-divider">{{ __('auth_pages.login.or') }}</div>

        <p class="text-center text-base">
            {{ __('auth_pages.login.no_account') }}
            <a href="{{ route('register') }}" class="font-bold underline">{{ __('auth_pages.login.sign_up') }}</a>
        </p>
    </form>
</x-auth-shell>
