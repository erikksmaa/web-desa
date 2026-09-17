<?php

namespace Database\Factories;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Agenda> */
class AgendaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'title' => fake()->sentence(5),
            'slug' => fake()->unique()->slug(5),
            'description' => fake()->paragraph(),
            'location' => 'Balai Desa (contoh)',
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(2),
            'status' => Agenda::STATUS_DRAFT,
        ];
    }
}
