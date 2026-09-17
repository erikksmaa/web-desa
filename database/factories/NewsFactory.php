<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<News> */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'news_category_id' => NewsCategory::factory(),
            'user_id' => null,
            'title' => fake()->sentence(6),
            'slug' => fake()->unique()->slug(6),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'thumbnail' => null,
            'status' => News::STATUS_DRAFT,
            'published_at' => null,
            'views' => 0,
        ];
    }
}
