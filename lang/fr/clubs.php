<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clubs
    |--------------------------------------------------------------------------
    */

    'index' => [
        'title' => 'Mes clubs',
        'members' => ':count membre|:count membres',
        'animator_badge' => 'prof',
        'empty' => 'Vous n’êtes dans aucun club pour l’instant. Créez le vôtre, ou demandez à votre prof de vous ajouter.',
        'create' => '+ Créer un club',
    ],

    'create' => [
        'title' => 'Nouveau club',
        'close' => 'Fermer',
        'fields' => [
            'name' => 'Nom du club',
            'city' => 'Ville',
            'description' => 'Description',
            'logo' => 'Logo',
        ],
        'placeholders' => [
            'name' => 'ex. Échecs · Collège Jean-Moulin',
            'city' => 'ex. Lyon',
            'description' => 'Facultatif : jour, heure et lieu des séances',
        ],
        'logo_hint' => 'Facultatif · PNG, JPEG ou WebP, 2 Mo maximum',
        'animator_hint' => 'Vous serez le prof animateur de ce club.',
        'submit' => 'Créer le club',
        'created' => ':name créé · vous êtes prof animateur',
    ],

];
