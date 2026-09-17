<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Announcement> */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'title' => fake()->sentence(5),
            'slug' => fake()->unique()->slug(5),
            'content' => fake()->paragraph(),
            'attachment_path' => null,
            'attachment_original_name' => null,
            'status' => Announcement::STATUS_DRAFT,
            'published_at' => null,
            'expires_at' => null,
        ];
    }
}
