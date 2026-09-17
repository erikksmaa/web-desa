<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $authorId = User::where('email', 'admin@example.com')->value('id');
        foreach (['Jadwal pelayanan desa', 'Undangan musyawarah warga', 'Pemberitahuan perawatan fasilitas', 'Rencana perubahan jam layanan'] as $order => $title) {
            Announcement::withTrashed()->firstOrCreate(['slug' => 'contoh-'.Str::slug($title)], [
                'user_id' => $authorId,
                'title' => $title.' (Contoh)',
                'content' => 'Pengumuman fiktif untuk pengembangan CMS, bukan informasi pelayanan resmi.',
                'status' => $order < 3 ? Announcement::STATUS_PUBLISHED : Announcement::STATUS_DRAFT,
                'published_at' => $order < 3 ? now()->subDays($order + 1) : null,
                'expires_at' => $order === 2 ? now()->subDay() : null,
            ]);
        }
    }
}
