{{-- Planche de référence des fondations visuelles (thèmes 1a / 1b), hors production --}}
@php
    $piece_types = ['king', 'queen', 'rook', 'bishop', 'knight', 'pawn'];
@endphp
<x-layouts::app title="Styleguide">
    <header class="flex items-center justify-between gap-4 border-b-2 border-ink bg-surface px-4 py-3 md:px-8">
        <x-logo />
        <x-scheme-toggle />
    </header>

    <main class="mx-auto flex max-w-5xl flex-col gap-14 px-4 py-10 md:px-8">
        <h1 class="text-[46px] leading-[.96] font-bold tracking-[-.04em]">Styleguide</h1>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Boutons</h2>
            <div class="flex flex-wrap items-center gap-6">
                <button type="button" class="mx-btn mx-btn--primary">Faire l’appel</button>
                <button type="button" class="mx-btn">Saisir les résultats</button>
                <button type="button" class="mx-btn mx-btn--sm">+ Nouveau tournoi</button>
                <button type="button" class="mx-btn mx-btn--link">Réinitialiser</button>
                <button type="button" class="mx-btn" disabled>Désactivé</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Cartes</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="mx-card">
                    <p class="text-[13px] text-muted">Mon ELO</p>
                    <p class="text-[32px] leading-none font-bold tracking-[-.03em]">1 248</p>
                </div>
                <div class="mx-card mx-card--highlight">
                    <p class="text-[13px] text-muted">Carte surlignée</p>
                    <p class="text-[32px] leading-none font-bold tracking-[-.03em]">1 312</p>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Champs</h2>
            <div class="grid gap-6 sm:grid-cols-3">
                <label class="flex flex-col gap-2">
                    <span class="mx-label">Repos</span>
                    <input type="email" class="mx-input" placeholder="prenom.nom@college.fr">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="mx-label">Focus</span>
                    {{-- Ombre de :focus reproduite pour la planche ; au repos, un champ n'a pas d'ombre --}}
                    <input type="text" class="mx-input" style="box-shadow: var(--mx-sh)" value="JM-4821">
                </label>
                <label class="flex flex-col gap-2">
                    <span class="mx-label">Erreur</span>
                    <input type="password" class="mx-input" value="abc" aria-invalid="true" aria-describedby="styleguide-error">
                    <span id="styleguide-error" class="mx-error">Le mot de passe doit faire au moins 6 caractères.</span>
                </label>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Puces</h2>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="mx-chip" aria-pressed="true">Les miens</button>
                <button type="button" class="mx-chip" aria-pressed="false">Tout le club</button>
                <button type="button" class="mx-chip" aria-pressed="false">En cours</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Contrôle segmenté</h2>
            <div class="mx-seg" role="group" aria-label="Profil">
                <button type="button" class="mx-seg__item" aria-pressed="true">Élève</button>
                <button type="button" class="mx-seg__item" aria-pressed="false">Prof animateur</button>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Switch de mode</h2>
            <div class="flex flex-wrap gap-6">
                <x-mode-switch mode="manage" />
                <x-mode-switch mode="play" />
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Bascule de thème</h2>
            <x-scheme-toggle />
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Logo</h2>
            <div class="flex flex-wrap items-stretch gap-6">
                <div class="flex items-center p-6">
                    <x-logo />
                </div>
                <div class="mx-inverse flex items-center p-6">
                    <x-logo inverse />
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Toast</h2>
            <div class="flex">
                <div class="mx-toast" role="status">Lien d’invitation copié</div>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="mx-label">Pièces</h2>
            @foreach (['white', 'black'] as $side)
                <div class="flex w-max border-2 border-ink bg-[#ffffff]">
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <x-piece :type="$type" :side="$side" />
                        </span>
                    @endforeach
                </div>
            @endforeach
            <div class="flex w-max border-2 border-ink bg-[#0c0c0c]">
                @foreach (['white', 'black'] as $side)
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <x-piece :type="$type" :side="$side" />
                        </span>
                    @endforeach
                @endforeach
            </div>
            <p class="text-[13px] text-muted">Fichiers de <code>public/images/pieces</code> :</p>
            <div class="flex w-max border-2 border-ink bg-[#ffffff]">
                @foreach (['white', 'black'] as $side)
                    @foreach ($piece_types as $type)
                        <span class="grid size-10 place-items-center">
                            <img src="{{ asset("images/pieces/{$side}-{$type}.svg") }}" alt="" class="size-8">
                        </span>
                    @endforeach
                @endforeach
            </div>
        </section>
    </main>
</x-layouts::app>
