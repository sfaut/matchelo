<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'last_name' => 'Faut',
            'first_name' => 'Sébastien',
            'email' => 'sebastien.faut@gmail.com',
            'civility_id' => 4,
            'is_administrator' => true,
            'password_hash' => Hash::make('MDP-TMP'),
        ]);
    }
}
