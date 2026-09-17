<?php

namespace Database\Factories;

use App\Models\GalleryAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryAlbum> */
class GalleryAlbumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(4),
            'description' => 'Album contoh pengembangan.',
            'cover_image' => null,
            'event_date' => now()->toDateString(),
            'status' => GalleryAlbum::STATUS_DRAFT,
            'sort_order' => 0,
        ];
    }
}
