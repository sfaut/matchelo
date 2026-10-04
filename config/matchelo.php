<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thèmes
    |--------------------------------------------------------------------------
    |
    | Un thème est un fichier CSS de variables (couleurs, typographie,
    | espacements…) placé dans le dossier des thèmes, relatif à la racine du
    | projet. Vite compile chaque fichier de ce dossier (vite.config.js).
    |
    | Le thème par défaut s'applique tant que l'utilisateur n'en a pas choisi
    | un autre ; un nom inconnu se replie sur le premier thème disponible.
    |
    */

    'themes_directory' => 'resources/css/themes',

    'default_theme' => env('MATCHELO_THEME', 'black-and-white'),

];
