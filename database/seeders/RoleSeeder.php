<?php

namespace Database\Seeders;

use App\Models\User\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'ANIMATOR', 'name' => 'Animateur'],
            ['id' => 2, 'code' => 'PLAYER', 'name' => 'Joueur'],
        ];

        foreach ($data as $record) {
            Role::create($record);
        }
    }
}
