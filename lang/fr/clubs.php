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

    'form' => [
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
        'remove_logo' => 'Retirer le logo',
    ],

    'create' => [
        'title' => 'Nouveau club',
        'animator_hint' => 'Vous serez le prof animateur de ce club.',
        'submit' => 'Créer le club',
        'created' => ':name créé · vous êtes prof animateur',
    ],

    'edit' => [
        'title' => 'Modifier le club',
        'link' => 'Modifier',
        'code' => 'Code d’invitation',
        'code_hint' => 'Donnez ce code à vos élèves pour qu’ils rejoignent le club.',
        'submit' => 'Enregistrer',
        'updated' => ':name mis à jour',
    ],

];
