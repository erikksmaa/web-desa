<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;

class GalleryPhotoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $albums = GalleryAlbum::whereIn('slug', ['contoh-kegiatan-balai-desa', 'contoh-kerja-bakti-lingkungan', 'contoh-musyawarah-warga'])->get();
        foreach ($albums as $album) {
            for ($number = 1; $number <= 4; $number++) {
                GalleryPhoto::firstOrCreate([
                    'gallery_album_id' => $album->id,
                    'image_path' => 'development/placeholders/gallery/'.$album->slug.'-'.$number.'.jpg',
                ], [
                    'caption' => 'Foto contoh '.$number.'; berkas belum tersedia.',
                    'alt_text' => 'Placeholder dokumentasi kegiatan desa',
                    'sort_order' => $number,
                ]);
            }
        }
    }
}
