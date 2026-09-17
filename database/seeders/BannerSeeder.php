<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        foreach (['Informasi desa', 'Pelayanan masyarakat'] as $order => $title) {
            Banner::firstOrCreate(['image_path' => 'development/placeholders/banners/'.Str::slug($title).'.jpg'], [
                'title' => $title.' (Contoh)',
                'subtitle' => 'Placeholder pengembangan; gambar belum tersedia.',
                'is_active' => false,
                'sort_order' => $order,
            ]);
        }
    }
}
