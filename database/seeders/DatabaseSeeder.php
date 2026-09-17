<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('Development seeding is restricted to APP_ENV=local.');

            return;
        }

        DB::transaction(function (): void {
            $this->call([
                AdminUserSeeder::class,
                NewsCategorySeeder::class,
                NewsSeeder::class,
                AnnouncementSeeder::class,
                AgendaSeeder::class,
                DocumentCategorySeeder::class,
                DocumentSeeder::class,
                GalleryAlbumSeeder::class,
                GalleryPhotoSeeder::class,
                VillageOfficialSeeder::class,
                BannerSeeder::class,
                SettingSeeder::class,
            ]);
        });
    }
}
