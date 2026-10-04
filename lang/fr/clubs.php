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

    'dashboard' => [
        'title' => 'Tableau de bord · :club',
        'date_format' => 'dddd Do MMMM',
        'greeting' => 'Bonjour :name',
        'add_player' => '+ Ajouter un élève',
        'today_session' => 'Séance du jour',
        'no_session' => 'Pas de séance aujourd’hui. Le pointage des présences arrive bientôt.',
        'live_tournament' => 'Tournoi en cours',
        'no_tournament' => 'Aucun tournoi en cours.',
        'to_handle' => 'À traiter',
        'nothing_to_handle' => 'Rien à traiter.',
        'attendance' => 'Assiduité · 5 dernières séances',
        'to_follow_up' => 'À relancer',
        'no_sessions_yet' => 'Aucune séance enregistrée.',
        'ranking' => 'Classement du club',
    ],

    'players' => [
        'title' => 'Ajouter un élève · :club',
        'search_label' => 'Trouver un joueur',
        'search_placeholder' => 'Chercher un joueur par nom…',
        'already_on_matchelo' => 'Déjà sur Matchelo',
        'no_club' => 'Sans club',
        'add' => 'Ajouter',
        'no_result' => 'Aucun joueur trouvé. Créez-le ci-dessous.',
        'create_title' => 'Nouveau joueur',
        'starting_elo' => 'ELO de départ',
        'create_submit' => 'Créer et ajouter',
        'search_first' => 'Cherchez d’abord : chaque joueur garde un seul ELO.',
        'members' => ':count membre|:count membres',
        'no_account' => 'sans compte',
        'added' => ':name ajouté au club',
        'created' => ':name créé et ajouté au club',
    ],

];
