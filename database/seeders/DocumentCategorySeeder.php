<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development sample seeding is restricted to APP_ENV=local.');

            return;
        }

        foreach (['Peraturan Desa', 'Laporan Desa', 'Formulir Layanan'] as $order => $name) {
            DocumentCategory::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'Kategori berkas contoh pengembangan.',
                'sort_order' => $order,
            ]);
        }
    }
}
