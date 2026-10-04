<?php

namespace App\Support;

use Illuminate\Container\Attributes\Config;
use RuntimeException;

/**
 * Thèmes visuels de l'application : un thème est un fichier CSS de variables
 * ({dossier des thèmes}/{thème}.css), chargé par le layout après app.css.
 */
class Themes
{
    /**
     * @param  string  $directory
     *                             dossier des fichiers de thème, relatif à la racine du projet (matchelo.themes_directory)
     * @param  string|null  $defaultTheme
     *                                     thème appliqué par défaut (matchelo.default_theme)
     */
    public function __construct(
        #[Config('matchelo.themes_directory')]
        private readonly string $directory,
        #[Config('matchelo.default_theme')]
        private readonly ?string $defaultTheme,
    ) {
        //
    }

    /**
     * Liste les thèmes disponibles, d'après les fichiers du dossier des thèmes.
     *
     * @return list<string>
     *                      noms des thèmes (nom de fichier sans extension), triés
     */
    public function available(): array
    {
        $theme_files = glob(base_path("{$this->directory}/*.css")) ?: [];
        $theme_names = array_map(fn (string $theme_file): string => basename($theme_file, '.css'), $theme_files);
        sort($theme_names);

        return $theme_names;
    }

    /**
     * Donne le thème à appliquer : le thème par défaut s'il existe, sinon le premier
     * thème disponible.
     *
     * @todo Lire le réglage de l'utilisateur connecté quand les comptes existeront.
     *
     * @throws RuntimeException si aucun fichier de thème n'existe
     */
    public function current(): string
    {
        $available_themes = $this->available();

        if ($available_themes === []) {
            throw new RuntimeException("Aucun thème dans {$this->directory}");
        }

        return in_array($this->defaultTheme, $available_themes, true) ? $this->defaultTheme : $available_themes[0];
    }

    /**
     * Donne le chemin de la feuille de style d'un thème, tel que l'attend @vite.
     *
     * @param  string|null  $theme
     *                              nom du thème ; le thème courant si null
     */
    public function stylesheet(?string $theme = null): string
    {
        $theme ??= $this->current();

        return "{$this->directory}/{$theme}.css";
    }
}
