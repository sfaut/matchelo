<?php

namespace Database\Seeders;

use App\Models\User\Civility;
use Illuminate\Database\Seeder;

class CivilitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'code' => 'UNDEFINED', 'name' => 'Indéfini', 'name_short' => 'Indéfini', 'sex_id' => 1],
            ['id' => 2, 'code' => 'MISS', 'name' => 'Mademoiselle', 'name_short' => 'Mlle', 'sex_id' => 2],
            ['id' => 3, 'code' => 'MADAM', 'name' => 'Madame', 'name_short' => 'Mme', 'sex_id' => 2],
            ['id' => 4, 'code' => 'MISTER', 'name' => 'Monsieur', 'name_short' => 'M.', 'sex_id' => 3],
        ];

        foreach ($data as $record) {
            Civility::create($record);
        }
    }
}
