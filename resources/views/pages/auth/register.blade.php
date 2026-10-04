<?php

use App\Models\User;
use App\Models\User\Civility;
use App\Notifications\AccountCreated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Page d'inscription : civilité, nom, prénom et e-mail ; le mot de passe est généré et envoyé par e-mail.
 */
new class extends Component
{
    /**
     * Longueur du mot de passe généré (lettres et chiffres).
     *
     * @var int
     */
    public const int GENERATED_PASSWORD_LENGTH = 12;

    /**
     * Identifiant de la civilité choisie (chaîne vide tant qu'aucune n'est choisie).
     */
    public string $civilityId = '';

    /**
     * Nom de famille saisi.
     */
    public string $lastName = '';

    /**
     * Prénom saisi.
     */
    public string $firstName = '';

    /**
     * E-mail saisi.
     */
    public string $email = '';

    /**
     * Civilités proposées, dans l'ordre de la table.
     *
     * @return Collection<int, Civility>
     */
    #[Computed]
    public function civilities(): Collection
    {
        return Civility::query()->orderBy('id')->get();
    }

    /**
     * Crée le compte avec un mot de passe généré, l'envoie par e-mail, puis renvoie vers la
     * connexion avec l'e-mail prérempli.
     */
    public function register(): void
    {
        $this->lastName = trim($this->lastName);
        $this->firstName = trim($this->firstName);
        $this->email = Str::lower(trim($this->email));

        $this->validate();

        $password = Str::password(self::GENERATED_PASSWORD_LENGTH, symbols: false);

        // Création et envoi ensemble : sans e-mail, le compte serait inutilisable
        $user = DB::transaction(function () use ($password): User {
            $user = User::create([
                'civility_id' => (int) $this->civilityId,
                'last_name' => $this->lastName,
                'first_name' => $this->firstName,
                'email' => $this->email,
                'password_hash' => $password,
            ]);

            $user->notify(new AccountCreated($password));

            return $user;
        });

        session()->flash('registered_email', $user->email);

        $this->redirectRoute('login');
    }

    /**
     * Règles de validation du formulaire.
     *
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'civilityId' => ['required', 'integer', Rule::exists(Civility::class, 'id')],
            'lastName' => ['required', 'string', 'max:30'],
            'firstName' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique(User::class, 'email')],
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
            'civilityId' => __('auth_pages.fields.civility'),
            'lastName' => __('auth_pages.fields.last_name'),
            'firstName' => __('auth_pages.fields.first_name'),
            'email' => __('auth_pages.fields.email'),
        ];
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('auth_pages.register.title'));
    }
};
?>

<x-auth-shell :panel-title="__('auth_pages.register.panel_title')" :panel-text="__('auth_pages.register.panel_text')">
    <form wire:submit="register" class="flex flex-col gap-4" novalidate>
        <h1 class="text-figure tracking-heading">{{ __('auth_pages.register.title') }}</h1>

        <div class="flex flex-col gap-1.5">
            <label for="civility" class="mx-label">{{ __('auth_pages.fields.civility') }}</label>
            <select id="civility" wire:model="civilityId" class="mx-input" @error('civilityId') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($this->civilities as $civility)
                    <option value="{{ $civility->id }}" wire:key="civility-{{ $civility->id }}">{{ __("civilities.{$civility->code}") }}</option>
                @endforeach
            </select>
            @error('civilityId')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="last-name" class="mx-label">{{ __('auth_pages.fields.last_name') }}</label>
                <input id="last-name" type="text" wire:model="lastName" autocomplete="family-name" maxlength="30" class="mx-input" @error('lastName') aria-invalid="true" @enderror>
                @error('lastName')
                    <p class="mx-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="first-name" class="mx-label">{{ __('auth_pages.fields.first_name') }}</label>
                <input id="first-name" type="text" wire:model="firstName" autocomplete="given-name" maxlength="30" class="mx-input" @error('firstName') aria-invalid="true" @enderror>
                @error('firstName')
                    <p class="mx-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="email" class="mx-label">{{ __('auth_pages.fields.email') }}</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                maxlength="100"
                placeholder="{{ __('auth_pages.email_placeholder') }}"
                class="mx-input"
                aria-describedby="email-help"
                @error('email') aria-invalid="true" @enderror
            >
            <p id="email-help" class="text-sm text-muted">{{ __('auth_pages.register.password_by_email') }}</p>
            @error('email')
                <p class="mx-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="mx-btn mx-btn--primary mt-1 w-full p-3.5 text-xl" wire:loading.attr="disabled">
            {{ __('auth_pages.register.submit') }}
        </button>

        <p class="text-center text-base">
            {{ __('auth_pages.register.already_registered') }}
            <a href="{{ route('login') }}" class="font-bold underline">{{ __('auth_pages.register.log_in') }}</a>
        </p>
    </form>
</x-auth-shell>
