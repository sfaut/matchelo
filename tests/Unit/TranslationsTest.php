<?php

use Illuminate\Support\Arr;

/**
 * Donne le dossier des langues du projet.
 */
function langPath(): string
{
    return dirname(__DIR__, 2).'/lang';
}

/**
 * Liste les langues traduites, l'anglais (référence) excepté, indexées pour les jeux de données Pest.
 *
 * @return array<string, array{string}>
 */
function translatedLocales(): array
{
    $locales = array_map(basename(...), glob(langPath().'/*', GLOB_ONLYDIR) ?: []);

    return collect($locales)
        ->reject(fn (string $locale): bool => $locale === 'en')
        ->mapWithKeys(fn (string $locale): array => [$locale => [$locale]])
        ->all();
}

/**
 * Charge un fichier de langue, aplati en clefs pointées.
 *
 * @return array<string, mixed>
 */
function translationLines(string $locale, string $file): array
{
    return Arr::dot(require langPath()."/{$locale}/{$file}");
}

/**
 * Liste les paramètres (:attribute, :count…) d'une ligne de traduction, triés.
 *
 * @return list<string>
 */
function translationPlaceholders(string $line): array
{
    preg_match_all('/:([a-z_]+)/i', $line, $matches);
    $placeholders = array_values(array_unique($matches[1]));
    sort($placeholders);

    return $placeholders;
}

test('every locale has the same language files as english', function (string $locale) {
    $english_files = array_map(basename(...), glob(langPath().'/en/*.php'));
    $locale_files = array_map(basename(...), glob(langPath()."/{$locale}/*.php"));

    expect($locale_files)->toEqualCanonicalizing($english_files);
})->with(translatedLocales());

test('every locale translates every english line with the same placeholders', function (string $locale) {
    foreach (glob(langPath().'/en/*.php') as $english_file) {
        $file = basename($english_file);
        $english_lines = translationLines('en', $file);
        $locale_lines = translationLines($locale, $file);

        expect(array_keys($locale_lines))->toEqualCanonicalizing(array_keys($english_lines), "{$locale}/{$file}");

        foreach ($english_lines as $key => $english_line) {
            // Tableau vide (ex. validation.attributes) : rien à traduire, la structure suffit
            if (! is_string($english_line)) {
                expect($locale_lines[$key])->toBe($english_line);

                continue;
            }

            expect(translationPlaceholders($locale_lines[$key]))
                ->toBe(translationPlaceholders($english_line), "{$locale}/{$file} : {$key}");
        }
    }
})->with(translatedLocales());
