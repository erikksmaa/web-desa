<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Banner> */
class BannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Banner contoh pengembangan',
            'subtitle' => null,
            'image_path' => 'development/placeholders/banners/'.fake()->uuid().'.jpg',
            'cta_label' => null,
            'cta_url' => null,
            'is_active' => false,
            'sort_order' => 0,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
