<?php

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ajout d'élèves à un club (ClubPolicy::manage) : un joueur déjà inscrit, trouvé par son nom,
 * ou un nouveau joueur sans compte, créé avec un ELO de départ. Chaque joueur garde un seul ELO.
 */
new class extends Component
{
    /**
     * Longueur minimale de la recherche, en caractères.
     *
     * @var int
     */
    public const int MIN_SEARCH_LENGTH = 2;

    /**
     * Nombre maximal de joueurs proposés.
     *
     * @var int
     */
    public const int MAX_RESULTS = 6;

    /**
     * Club géré.
     */
    #[Locked]
    public Club $club;

    /**
     * Recherche par nom (prénom et / ou nom).
     */
    public string $search = '';

    /**
     * Nom de famille du nouveau joueur.
     */
    public string $lastName = '';

    /**
     * Prénom du nouveau joueur.
     */
    public string $firstName = '';

    /**
     * ELO de départ du nouveau joueur.
     */
    public string $startingElo = '1200';

    /**
     * Vérifie le droit de gérer le club.
     *
     * @throws AuthorizationException si l'utilisateur n'anime pas le club et n'est pas administrateur
     */
    public function mount(Club $club): void
    {
        $this->authorize('manage', $club);

        $this->club = $club;
    }

    /**
     * Joueurs hors du club dont le prénom ou le nom contient chaque mot de la recherche.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function results(): Collection
    {
        // % et _ sont retirés : jokers de LIKE, ils n'ont pas leur place dans un nom
        $terms = Str::of($this->search)->replace(['%', '_'], '')->squish()->explode(' ')->filter();

        if (mb_strlen($terms->implode('')) < self::MIN_SEARCH_LENGTH) {
            return new Collection;
        }

        $query = User::query()
            ->with('clubs')
            ->whereDoesntHave('clubs', fn (Builder $clubs): Builder => $clubs->whereKey($this->club->id));

        foreach ($terms as $term) {
            $query->where(fn (Builder $names): Builder => $names
                ->whereLike('first_name', "%{$term}%")
                ->orWhereLike('last_name', "%{$term}%"));
        }

        return $query->orderBy('last_name')->orderBy('first_name')->limit(self::MAX_RESULTS)->get();
    }

    /**
     * Membres du club, par nom.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return $this->club->members()->orderBy('last_name')->orderBy('first_name')->get();
    }

    /**
     * Identifiant du rôle d'animateur, pour le badge « prof » des membres.
     */
    #[Computed]
    public function animatorRoleId(): ?int
    {
        return Role::idFor(Role::ANIMATOR);
    }

    /**
     * Ajoute un joueur existant au club, comme joueur.
     *
     * @param  int  $userId  joueur choisi dans les résultats
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function add(int $userId): void
    {
        $this->authorize('manage', $this->club);

        $player = User::query()->findOrFail($userId);

        if (! $this->club->members()->whereKey($player->id)->exists()) {
            $this->club->members()->attach($player, ['role_id' => Role::idFor(Role::PLAYER)]);
        }

        $this->backWithToast(__('clubs.players.added', ['name' => $player->fullName()]));
    }

    /**
     * Crée un joueur sans compte avec son ELO de départ, puis l'ajoute au club.
     *
     * @throws AuthorizationException si le droit a été retiré entre-temps
     */
    public function create(): void
    {
        $this->authorize('manage', $this->club);

        $this->lastName = trim($this->lastName);
        $this->firstName = trim($this->firstName);

        $this->validate();

        $player = User::create([
            'last_name' => $this->lastName,
            'first_name' => $this->firstName,
            'elo_rating' => (int) $this->startingElo,
        ]);

        $this->club->members()->attach($player, ['role_id' => Role::idFor(Role::PLAYER)]);

        $this->backWithToast(__('clubs.players.created', ['name' => $player->fullName()]));
    }

    /**
     * Règles de validation du nouveau joueur.
     *
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'lastName' => ['required', 'string', 'max:30'],
            'firstName' => ['required', 'string', 'max:30'],
            'startingElo' => ['required', 'integer', 'between:100,3000'],
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
            'lastName' => __('auth_pages.fields.last_name'),
            'firstName' => __('auth_pages.fields.first_name'),
            'startingElo' => __('clubs.players.starting_elo'),
        ];
    }

    /**
     * Recharge la page avec un toast de confirmation (recherche et formulaire remis à zéro).
     */
    protected function backWithToast(string $message): void
    {
        session()->flash('toast', $message);

        $this->redirectRoute('clubs.players.create', $this->club);
    }

    /**
     * Rend la page, avec son titre.
     */
    public function render(): View
    {
        return $this->view()->title(__('clubs.players.title', ['club' => $this->club->name]));
    }
};
?>

<x-app-shell mode="manage" :club="$club" section="players">
    <div class="flex flex-col gap-6">
        <h1 class="text-title">{{ __('clubs.players.title', ['club' => $club->name]) }}</h1>

        <div class="grid items-start gap-6 lg:grid-cols-2">
            <section class="flex flex-col gap-3">
                <label for="search" class="mx-label">{{ __('clubs.players.search_label') }}</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('clubs.players.search_placeholder') }}"
                    autocomplete="off"
                    class="mx-input"
                >

                @if ($this->results->isNotEmpty())
                    <h2 class="mx-label">{{ __('clubs.players.already_on_matchelo') }}</h2>
                    @foreach ($this->results as $result)
                        <div class="mx-card flex items-center justify-between gap-2.5 px-2.5 py-2" wire:key="result-{{ $result->id }}">
                            <div class="flex min-w-0 flex-col">
                                <b class="truncate">{{ $result->fullName() }} · {{ Number::format($result->elo_rating) }}</b>
                                <span class="truncate text-xs text-muted">
                                    {{ $result->clubs->isEmpty() ? __('clubs.players.no_club') : $result->clubs->pluck('name')->join(', ') }}
                                </span>
                            </div>
                            <button type="button" class="mx-btn mx-btn--primary mx-btn--sm flex-none" wire:click="add({{ $result->id }})">
                                {{ __('clubs.players.add') }}
                            </button>
                        </div>
                    @endforeach
                @elseif (mb_strlen(trim($search)) >= $this::MIN_SEARCH_LENGTH)
                    <p class="text-md text-muted">{{ __('clubs.players.no_result') }}</p>
                @endif

                <form wire:submit="create" class="flex flex-col gap-3 mx-border bg-surface p-3.5" novalidate>
                    <h2 class="font-bold">{{ __('clubs.players.create_title') }}</h2>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex min-w-0 flex-col gap-1.5">
                            <label for="last-name" class="mx-label">{{ __('auth_pages.fields.last_name') }}</label>
                            <input id="last-name" type="text" wire:model="lastName" maxlength="30" class="mx-input" @error('lastName') aria-invalid="true" @enderror>
                            @error('lastName')
                                <p class="mx-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex min-w-0 flex-col gap-1.5">
                            <label for="first-name" class="mx-label">{{ __('auth_pages.fields.first_name') }}</label>
                            <input id="first-name" type="text" wire:model="firstName" maxlength="30" class="mx-input" @error('firstName') aria-invalid="true" @enderror>
                            @error('firstName')
                                <p class="mx-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="starting-elo" class="mx-label">{{ __('clubs.players.starting_elo') }}</label>
                            <input id="starting-elo" type="number" min="100" max="3000" wire:model="startingElo" class="mx-input w-28" @error('startingElo') aria-invalid="true" @enderror>
                        </div>
                        <button type="submit" class="mx-btn" wire:loading.attr="disabled">{{ __('clubs.players.create_submit') }}</button>
                    </div>
                    @error('startingElo')
                        <p class="mx-error">{{ $message }}</p>
                    @enderror

                    <p class="text-sm">{{ __('clubs.players.search_first') }}</p>
                </form>
            </section>

            <section class="flex flex-col gap-2.5">
                <h2 class="mx-label">{{ trans_choice('clubs.players.members', $this->members->count()) }}</h2>
                @foreach ($this->members as $member)
                    <div class="flex items-center justify-between gap-2 mx-border-b py-2" wire:key="member-{{ $member->id }}">
                        <b class="min-w-0 truncate">{{ $member->fullName() }}</b>
                        <div class="flex flex-none items-center gap-2">
                            @unless ($member->hasAccount())
                                <span class="mx-badge text-muted">{{ __('clubs.players.no_account') }}</span>
                            @endunless
                            @if ($member->pivot->role_id === $this->animatorRoleId)
                                <span class="mx-badge">{{ __('clubs.index.animator_badge') }}</span>
                            @endif
                            <span class="tabular-nums text-muted">{{ Number::format($member->elo_rating) }}</span>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</x-app-shell>
