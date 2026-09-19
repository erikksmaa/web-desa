<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Document;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\Setting;
use App\Models\VillageOfficial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_homepage_has_all_sections_and_graceful_hero(): void
    {
        Storage::fake('public');
        $this->get(route('home'))->assertOk()->assertSeeText('Selamat datang')
            ->assertSeeTextInOrder(['Berita Terbaru', 'Pengumuman', 'Agenda Mendatang', 'Berkas Publik', 'Galeri Kegiatan'])
            ->assertDontSee('data-bs-slide=', false)->assertDontSee('sedang dalam pengembangan');
    }

    public function test_banner_zero_one_multiple_and_schedule_ordering(): void
    {
        Storage::fake('public');
        $this->travelTo(now()->startOfSecond());
        $one = Banner::factory()->create(['title' => 'One Banner', 'is_active' => true, 'sort_order' => 5, 'starts_at' => now(), 'cta_label' => 'Unsafe CTA', 'cta_url' => 'javascript:alert(1)']);
        $this->get(route('home'))->assertOk()->assertSee('One Banner')->assertDontSee('data-bs-slide=', false)->assertDontSee('Unsafe CTA')->assertDontSee('src="/storage/development', false);
        $two = Banner::factory()->create(['title' => 'Two Banner', 'is_active' => true, 'sort_order' => 1, 'cta_label' => 'Read More', 'cta_url' => 'https://example.com/news']);
        Banner::factory()->create(['title' => 'Inactive Banner', 'is_active' => false]);
        Banner::factory()->create(['title' => 'Future Banner', 'is_active' => true, 'starts_at' => now()->addSecond()]);
        Banner::factory()->create(['title' => 'Expired Banner', 'is_active' => true, 'ends_at' => now()]);
        $this->get(route('home'))->assertOk()->assertSeeInOrder(['Two Banner', 'One Banner'])->assertSee('data-bs-slide="next"', false)->assertSee('https://example.com/news')->assertDontSee('Inactive Banner')->assertDontSee('Future Banner')->assertDontSee('Expired Banner')->assertViewHas('banners', fn ($rows) => $rows->pluck('id')->all() === [$two->id, $one->id]);
        Banner::factory()->count(12)->create(['is_active' => true, 'sort_order' => 10]);
        $this->get(route('home'))->assertViewHas('banners', fn ($rows) => $rows->count() === 10);
    }

    public function test_welcome_uses_only_active_head_and_is_safe_without_identity(): void
    {
        Storage::fake('public');
        Setting::create(['key' => 'village.head_welcome', 'value' => 'Welcome <script>bad()</script>', 'type' => 'text', 'group' => 'village']);
        VillageOfficial::factory()->create(['name' => 'Inactive Head', 'is_village_head' => true, 'is_active' => false]);
        VillageOfficial::factory()->create(['name' => 'Ordinary Official', 'is_village_head' => false, 'is_active' => true]);
        $this->get(route('home'))->assertOk()->assertSeeText('Sambutan Kepala Desa')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>bad()</script>', false)->assertDontSee('Inactive Head')->assertDontSee('Ordinary Official');
        VillageOfficial::factory()->create(['name' => 'Canonical Head', 'position' => 'Kepala Desa', 'is_village_head' => true, 'is_active' => true, 'photo_path' => 'missing.jpg']);
        $this->get(route('home'))->assertOk()->assertSeeText('Canonical Head')->assertDontSee('src="/storage/missing.jpg"', false);
    }

    public function test_content_visibility_limits_and_order_use_existing_rules(): void
    {
        Storage::fake('public');
        $this->travelTo(now()->startOfSecond());
        foreach ([News::class, Announcement::class, Document::class] as $model) {
            for ($i = 0; $i < 6; $i++) {
                $model::factory()->create(['title' => class_basename($model).' visible '.$i, 'status' => 'published', 'published_at' => now()->subMinutes($i + 1)]);
            }
            $model::factory()->create(['title' => class_basename($model).' hidden draft', 'status' => 'draft', 'published_at' => now()]);
            $model::factory()->create(['title' => class_basename($model).' hidden future', 'status' => 'published', 'published_at' => now()->addDay()]);
            $model::factory()->create(['title' => class_basename($model).' hidden null', 'status' => 'published', 'published_at' => null]);
            $model::factory()->create(['title' => class_basename($model).' hidden deleted', 'status' => 'published', 'published_at' => now()])->delete();
        }
        Announcement::factory()->create(['title' => 'hidden expired', 'status' => 'published', 'published_at' => now()->subDay(), 'expires_at' => now()]);
        Agenda::factory()->create(['title' => 'Ongoing Agenda', 'status' => 'published', 'start_at' => now()->subHour(), 'end_at' => now()->addHour()]);
        for ($i = 1; $i < 7; $i++) {
            Agenda::factory()->create(['title' => 'Agenda visible '.$i, 'status' => 'published', 'start_at' => now()->addDays($i), 'end_at' => null]);
        }
        Agenda::factory()->create(['title' => 'hidden old agenda', 'status' => 'published', 'start_at' => now()->subDays(2), 'end_at' => now()->subDay()]);
        Agenda::factory()->create(['title' => 'hidden draft agenda', 'status' => 'draft', 'start_at' => now()->addHour()]);
        Agenda::factory()->create(['title' => 'hidden deleted agenda', 'status' => 'published', 'start_at' => now()->addHour()])->delete();
        for ($i = 0; $i < 5; $i++) {
            GalleryAlbum::factory()->create(['title' => 'Gallery visible '.$i, 'status' => 'published', 'event_date' => now()->subDays($i)]);
        }
        GalleryAlbum::factory()->create(['title' => 'hidden draft gallery', 'status' => 'draft']);
        $response = $this->get(route('home'))->assertOk()->assertDontSeeText('hidden');
        foreach (['newsItems' => 3, 'announcements' => 4, 'agendas' => 4, 'documents' => 4, 'albums' => 3] as $key => $limit) {
            $response->assertViewHas($key, fn ($items) => $items->count() === $limit);
        }
        $response->assertSeeInOrder(['News visible 0', 'News visible 1', 'News visible 2'])->assertSeeInOrder(['Ongoing Agenda', 'Agenda visible 1', 'Agenda visible 2', 'Agenda visible 3'])->assertDontSee('News visible 3')->assertDontSee('Gallery visible 3');
        $document = Document::where('title', 'Document visible 0')->first();
        $response->assertSee(route('documents.download', $document), false);
        $this->assertSame(0, $document->download_count);
    }

    public function test_missing_images_fallback_and_gallery_photo_count(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::factory()->create(['status' => 'published', 'cover_image' => 'missing.jpg']);
        Storage::disk('public')->put('gallery/photo.jpg', 'image');
        GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => 'gallery/photo.jpg']);
        News::factory()->create(['status' => 'published', 'published_at' => now(), 'thumbnail' => 'missing-news.jpg']);
        $this->get(route('home'))->assertOk()->assertSee(Storage::disk('public')->url('gallery/photo.jpg'))->assertSeeText('1 foto')->assertDontSee('src="/storage/missing', false);
    }

    public function test_settings_are_batched_and_homepage_relationships_are_eager_loaded(): void
    {
        News::factory()->count(3)->create(['status' => 'published', 'published_at' => now()]);
        Document::factory()->count(4)->create(['status' => 'published', 'published_at' => now()]);
        GalleryAlbum::factory()->count(3)->create(['status' => 'published']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get(route('home'))->assertOk()->assertViewHas('newsItems', fn ($items) => $items->every(fn ($item) => $item->relationLoaded('category')))
            ->assertViewHas('documents', fn ($items) => $items->every(fn ($item) => $item->relationLoaded('category')))
            ->assertViewHas('albums', fn ($items) => $items->every(fn ($item) => $item->relationLoaded('firstPhoto')));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $settingsQueries = array_filter($queries, fn ($query) => str_contains($query['query'], 'from "settings"'));
        $this->assertCount(1, $settingsQueries);
        $this->assertLessThanOrEqual(12,count($queries));
    }
}
