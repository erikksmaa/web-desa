<?php

namespace Database\Factories;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryPhoto> */
class GalleryPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gallery_album_id' => GalleryAlbum::factory(),
            'image_path' => 'development/placeholders/gallery/'.fake()->uuid().'.jpg',
            'caption' => 'Foto contoh; berkas belum tersedia.',
            'alt_text' => 'Placeholder foto pengembangan',
            'sort_order' => 0,
        ];
    }
}
