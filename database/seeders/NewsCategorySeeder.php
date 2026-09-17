<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        foreach (['Pemerintahan Desa', 'Kegiatan Warga', 'Layanan Publik', 'Pembangunan'] as $order => $name) {
            NewsCategory::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'Kategori contoh untuk pengembangan CMS.',
                'sort_order' => $order,
            ]);
        }
    }
}
