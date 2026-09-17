<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Banner;
use App\Models\Document;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use App\Models\VillageOfficial;
use Database\Seeders\AgendaSeeder;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DocumentCategorySeeder;
use Database\Seeders\DocumentSeeder;
use Database\Seeders\GalleryAlbumSeeder;
use Database\Seeders\GalleryPhotoSeeder;
use Database\Seeders\NewsCategorySeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\VillageOfficialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoreSeederTest extends TestCase
{
    use RefreshDatabase;

    private function counts(): array
    {
        return collect([
            'users', 'news_categories', 'news', 'announcements', 'agendas',
            'document_categories', 'documents', 'gallery_albums', 'gallery_photos',
            'village_officials', 'banners', 'settings',
        ])->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all();
    }

    public function test_local_seeding_is_modest_repeatable_and_preserves_existing_edits(): void
    {
        $this->app->instance('env', 'local');
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $originalPassword = $admin->password;
        $this->seed(DatabaseSeeder::class);
        $expected = [
            'users' => 1, 'news_categories' => 4, 'news' => 8, 'announcements' => 4,
            'agendas' => 5, 'document_categories' => 3, 'documents' => 6,
            'gallery_albums' => 3, 'gallery_photos' => 12,
            'village_officials' => 6, 'banners' => 2, 'settings' => 16,
        ];
        $this->assertSame($expected, $this->counts());

        Setting::where('key', 'site.name')->update(['value' => 'Nama yang telah diedit']);
        $news = News::firstOrFail();
        $news->update(['title' => 'Judul yang telah diedit']);
        $news->delete();
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($expected, $this->counts());
        $this->assertSame($originalPassword, $admin->fresh()->password);
        $this->assertSame('Nama yang telah diedit', Setting::where('key', 'site.name')->value('value'));
        $this->assertSame('Judul yang telah diedit', News::withTrashed()->findOrFail($news->id)->title);
        $this->assertSoftDeleted($news);
        $this->assertSame(1, VillageOfficial::active()->where('is_village_head', true)->count());
    }

    public function test_placeholder_files_are_not_published_or_active(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Document::published()->count());
        $this->assertSame(0, GalleryAlbum::published()->count());
        $this->assertSame(0, Banner::active()->count());
        foreach (Document::all() as $document) {
            $this->assertStringStartsWith('development/placeholders/', $document->file_path);
        }
        $this->assertSame(0, News::whereNotNull('thumbnail')->count());
        $this->assertSame(0, VillageOfficial::whereNotNull('photo_path')->count());
        $this->assertSame(0, Agenda::whereColumn('end_at', '<', 'start_at')->count());

        $this->assertEqualsCanonicalizing([
            'site.name', 'site.tagline', 'site.logo', 'village.address', 'village.phone',
            'village.email', 'village.vision', 'village.mission', 'village.history',
            'village.head_welcome', 'social.facebook', 'social.instagram', 'social.youtube',
            'map.embed_url', 'footer.description', 'sotk.image',
        ], Setting::pluck('key')->all());
        $this->assertSame(0, Setting::whereIn('key', [
            'village.head_name', 'village.head_photo', 'village.head_position',
        ])->count());
    }

    public function test_existing_active_village_head_is_not_duplicated(): void
    {
        $this->app->instance('env', 'local');
        $head = VillageOfficial::factory()->create(['is_village_head' => true]);
        $this->seed(VillageOfficialSeeder::class);

        $this->assertTrue(VillageOfficial::active()->where('is_village_head', true)->sole()->is($head));
    }

    public function test_root_and_individual_business_seeders_refuse_production(): void
    {
        $this->app->instance('env', 'production');
        foreach ([
            DatabaseSeeder::class,
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
        ] as $seeder) {
            $this->app->make($seeder)->run();
        }
        foreach ($this->counts() as $count) {
            $this->assertSame(0, $count);
        }
    }
}
