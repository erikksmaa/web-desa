<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GalleryAlbumSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $authorId = User::where('email', 'admin@example.com')->value('id');
        foreach (['Kegiatan balai desa', 'Kerja bakti lingkungan', 'Musyawarah warga'] as $order => $title) {
            GalleryAlbum::firstOrCreate(['slug' => 'contoh-'.Str::slug($title)], [
                'user_id' => $authorId,
                'title' => $title.' (Contoh)',
                'description' => 'Album placeholder pengembangan; berkas foto belum tersedia.',
                'event_date' => now()->subDays(($order + 1) * 7)->toDateString(),
                'status' => GalleryAlbum::STATUS_DRAFT,
                'sort_order' => $order,
            ]);
        }
    }
}
