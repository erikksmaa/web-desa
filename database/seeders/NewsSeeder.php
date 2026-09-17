<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        $authorId = User::where('email', 'admin@example.com')->value('id');
        $categories = NewsCategory::whereIn('slug', ['pemerintahan-desa', 'kegiatan-warga', 'layanan-publik', 'pembangunan'])->orderBy('sort_order')->get();
        foreach ([
            'Musyawarah rencana kerja desa',
            'Kerja bakti lingkungan balai desa',
            'Informasi pelayanan administrasi',
            'Peninjauan sarana umum desa',
            'Rapat koordinasi perangkat desa',
            'Kegiatan membaca bersama',
            'Persiapan layanan akhir pekan',
            'Rencana perawatan jalan lingkungan',
        ] as $order => $title) {
            News::withTrashed()->firstOrCreate(['slug' => 'contoh-'.Str::slug($title)], [
                'news_category_id' => $categories[$order % $categories->count()]->id,
                'user_id' => $authorId,
                'title' => $title.' (Contoh)',
                'excerpt' => 'Contoh berita untuk pengembangan website desa.',
                'content' => 'Data fiktif untuk pengembangan. Kegiatan ini menggambarkan informasi yang nantinya dapat dikelola administrator desa.',
                'status' => $order < 6 ? News::STATUS_PUBLISHED : News::STATUS_DRAFT,
                'published_at' => $order < 6 ? now()->subDays($order + 1) : null,
            ]);
        }
    }
}
