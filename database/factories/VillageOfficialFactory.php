<?php

namespace Database\Factories;

use App\Models\VillageOfficial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VillageOfficial> */
class VillageOfficialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Perangkat Contoh '.fake()->unique()->numerify('####'),
            'position' => 'Staf Desa (contoh)',
            'photo_path' => null,
            'biography' => null,
            'is_village_head' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
