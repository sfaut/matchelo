<?php

namespace Database\Seeders;

use App\Models\User\Sex;
use Illuminate\Database\Seeder;

class SexSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'UNDEFINED', 'name' => 'Non défini', 'is_feminine' => false, 'is_masculine' => true],
            ['id' => 2, 'code' => 'WOMAN', 'name' => 'Femme', 'is_feminine' => true, 'is_masculine' => false],
            ['id' => 3, 'code' => 'MAN', 'name' => 'Homme', 'is_feminine' => false, 'is_masculine' => true],
        ];

        foreach ($data as $record) {
            Sex::create($record);
        }
    }
}
