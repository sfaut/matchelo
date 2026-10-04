<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clubs
    |--------------------------------------------------------------------------
    */

    'index' => [
        'title' => 'My clubs',
        'members' => ':count member|:count members',
        'animator_badge' => 'teacher',
        'empty' => 'You are not in any club yet. Create yours, or ask your teacher to add you.',
        'create' => '+ Create a club',
    ],

    'form' => [
        'close' => 'Close',
        'fields' => [
            'name' => 'Club name',
            'city' => 'City',
            'description' => 'Description',
            'logo' => 'Logo',
        ],
        'placeholders' => [
            'name' => 'e.g. Chess · Jean-Moulin school',
            'city' => 'e.g. Lyon',
            'description' => 'Optional: session day, time and place',
        ],
        'logo_hint' => 'Optional · PNG, JPEG or WebP, 2 MB max',
        'remove_logo' => 'Remove the logo',
    ],

    'create' => [
        'title' => 'New club',
        'animator_hint' => 'You will be the teacher running this club.',
        'submit' => 'Create the club',
        'created' => ':name created · you run this club',
    ],

    'edit' => [
        'title' => 'Edit the club',
        'link' => 'Edit',
        'code' => 'Invite code',
        'code_hint' => 'Give this code to your students so they can join the club.',
        'submit' => 'Save',
        'updated' => ':name updated',
    ],

    'dashboard' => [
        'title' => 'Dashboard · :club',
        'date_format' => 'dddd, MMMM Do',
        'greeting' => 'Hello :name',
        'add_player' => '+ Add a student',
        'today_session' => 'Today’s session',
        'no_session' => 'No session today. Attendance taking is coming soon.',
        'live_tournament' => 'Tournament in progress',
        'no_tournament' => 'No tournament in progress.',
        'to_handle' => 'To handle',
        'nothing_to_handle' => 'Nothing to handle.',
        'attendance' => 'Attendance · last 5 sessions',
        'to_follow_up' => 'To follow up',
        'no_sessions_yet' => 'No session recorded yet.',
        'ranking' => 'Club ranking',
    ],

    'players' => [
        'title' => 'Add a student · :club',
        'search_label' => 'Find a player',
        'search_placeholder' => 'Search a player by name…',
        'already_on_matchelo' => 'Already on Matchelo',
        'no_club' => 'No club',
        'add' => 'Add',
        'no_result' => 'No player found. Create them below.',
        'create_title' => 'New player',
        'starting_elo' => 'Starting ELO',
        'create_submit' => 'Create and add',
        'search_first' => 'Search first: each player keeps a single ELO.',
        'members' => ':count member|:count members',
        'no_account' => 'no account',
        'added' => ':name added to the club',
        'created' => ':name created and added to the club',
    ],

];
