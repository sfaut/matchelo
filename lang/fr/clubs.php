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
        'president_hint' => 'Vous serez président de ce club.',
        'submit' => 'Créer le club',
        'created' => ':name créé · vous en êtes président',
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
        'in_progress' => 'EN COURS',
        'not_checked' => '{0} Tout le monde est pointé|{1} :count élève pas encore pointé|[2,*] :count élèves pas encore pointés',
        'take_attendance' => '✓ Pointer les présents',
        'edit_attendance' => '✓ Modifier l’appel',
        'live_tournament' => 'Tournoi en cours',
        'no_tournament' => 'Aucun tournoi en cours.',
        'to_handle' => 'À traiter',
        'nothing_to_handle' => 'Rien à traiter.',
        'attendance' => 'Assiduité · 5 dernières séances',
        'average' => 'Moy. :rate',
        'today_short' => 'Auj.',
        'bar_date_format' => 'D/M',
        'to_follow_up' => 'À relancer',
        'absent_in_a_row' => 'absent :count fois de suite|absent aux :count dernières séances',
        'nobody_to_follow_up' => 'Tout le monde est venu récemment.',
        'all_attendance' => 'Toutes les présences →',
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

    'attendance' => [
        'title' => 'Appel · :club',
        'heading' => 'Appel',
        'sessions' => 'Séance',
        'today' => 'Aujourd’hui',
        'date_format' => 'ddd DD/MM',
        'present_out_of' => '/ :total présent|/ :total présents',
        'all_present' => 'Tous présents',
        'reset' => 'Réinitialiser',
        'search' => 'Chercher un élève…',
        'elo' => 'ELO :elo',
        'rate' => 'présent :count/:total',
        'present' => 'Présent ✓',
        'absent' => 'Absent',
        'new_student' => '+ Nouvel élève ? L’ajouter',
        'done' => 'Terminé',
        'saved' => '{0} Aucun présent · enregistré|{1} :count présent · enregistré|[2,*] :count présents · enregistré',
    ],

];
