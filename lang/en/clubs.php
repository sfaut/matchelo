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
        'president_hint' => 'You will be the president of this club.',
        'submit' => 'Create the club',
        'created' => ':name created · you are its president',
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
        'in_progress' => 'IN PROGRESS',
        'not_checked' => '{0} Everyone is checked in|{1} :count student not checked in yet|[2,*] :count students not checked in yet',
        'take_attendance' => '✓ Check in the students',
        'edit_attendance' => '✓ Edit the roll call',
        'live_tournament' => 'Tournament in progress',
        'no_tournament' => 'No tournament in progress.',
        'to_handle' => 'To handle',
        'nothing_to_handle' => 'Nothing to handle.',
        'attendance' => 'Attendance · last 5 sessions',
        'average' => 'Avg. :rate',
        'today_short' => 'Today',
        'bar_date_format' => 'M/D',
        'to_follow_up' => 'To follow up',
        'absent_in_a_row' => 'absent :count time in a row|absent the last :count sessions',
        'nobody_to_follow_up' => 'Everyone came recently.',
        'all_attendance' => 'All attendance →',
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

    'attendance' => [
        'title' => 'Roll call · :club',
        'heading' => 'Roll call',
        'sessions' => 'Session',
        'today' => 'Today',
        'date_format' => 'ddd MM/DD',
        'present_out_of' => '/ :total present|/ :total present',
        'all_present' => 'All present',
        'reset' => 'Reset',
        'search' => 'Find a student…',
        'elo' => 'ELO :elo',
        'rate' => 'present :count/:total',
        'present' => 'Present ✓',
        'absent' => 'Absent',
        'new_student' => '+ New student? Add them',
        'done' => 'Done',
        'saved' => '{0} No one present · saved|{1} :count present · saved|[2,*] :count present · saved',
    ],

];
