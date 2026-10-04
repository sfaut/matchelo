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

    'create' => [
        'title' => 'New club',
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
        'animator_hint' => 'You will be the teacher running this club.',
        'submit' => 'Create the club',
        'created' => ':name created · you run this club',
    ],

];
