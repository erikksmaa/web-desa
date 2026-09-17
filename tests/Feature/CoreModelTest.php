<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\VillageOfficial;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CoreModelTest extends TestCase
{
    use RefreshDatabase;

    public static function datedContent(): array
    {
        return [[News::class], [Announcement::class], [Document::class]];
    }

    #[DataProvider('datedContent')]
    public function test_published_scope_excludes_drafts_future_undated_and_deleted_content(string $model): void
    {
        $this->freezeTime();
        $visible = $model::factory()->create(['status' => 'published', 'published_at' => now()]);
        $model::factory()->create(['status' => 'draft', 'published_at' => now()->subDay()]);
        $model::factory()->create(['status' => 'published', 'published_at' => now()->addSecond()]);
        $model::factory()->create(['status' => 'published', 'published_at' => null]);
        $trashed = $model::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
        $trashed->delete();

        $this->assertSame([$visible->id], $model::published()->pluck('id')->all());
    }

    public function test_announcement_expiration_is_exclusive_at_current_time(): void
    {
        $this->freezeTime();
        $base = ['status' => Announcement::STATUS_PUBLISHED, 'published_at' => now()->subDay()];
        $noExpiry = Announcement::factory()->create($base);
        $future = Announcement::factory()->create($base + ['expires_at' => now()->addSecond()]);
        Announcement::factory()->create($base + ['expires_at' => now()]);
        Announcement::factory()->create($base + ['expires_at' => now()->subSecond()]);

        $this->assertEqualsCanonicalizing([$noExpiry->id, $future->id], Announcement::published()->pluck('id')->all());
    }

    public function test_agenda_and_album_publication_depend_on_status_not_event_date(): void
    {
        foreach ([Agenda::class, GalleryAlbum::class] as $model) {
            $published = $model::factory()->create(['status' => 'published']);
            $model::factory()->create(['status' => 'draft']);
            $this->assertSame([$published->id], $model::published()->pluck('id')->all());
        }
    }

    public function test_active_scope_filters_boolean_visibility_only(): void
    {
        foreach ([NewsCategory::class, DocumentCategory::class, VillageOfficial::class, Banner::class] as $model) {
            $active = $model::factory()->create(['is_active' => true]);
            $model::factory()->create(['is_active' => false]);
            $this->assertSame([$active->id], $model::active()->pluck('id')->all());
        }
    }

    public function test_dates_counters_and_flags_are_cast_after_database_round_trip(): void
    {
        $news = News::factory()->create(['published_at' => now(), 'views' => '17'])->fresh();
        $document = Document::factory()->create(['published_at' => now(), 'file_size' => '1024', 'download_count' => '8'])->fresh();
        $announcement = Announcement::factory()->create(['published_at' => now(), 'expires_at' => now()->addDay()])->fresh();
        $agenda = Agenda::factory()->create()->fresh();
        $banner = Banner::factory()->create(['starts_at' => now(), 'ends_at' => now()->addDay()])->fresh();
        $album = GalleryAlbum::factory()->create()->fresh();

        foreach ([$news->published_at, $document->published_at, $announcement->published_at,
            $announcement->expires_at, $agenda->start_at, $agenda->end_at,
            $banner->starts_at, $banner->ends_at, $album->event_date] as $date) {
            $this->assertInstanceOf(CarbonInterface::class, $date);
        }
        $this->assertSame('00:00:00', $album->event_date->format('H:i:s'));
        $this->assertSame(17, $news->views);
        $this->assertSame(1024, $document->file_size);
        $this->assertSame(8, $document->download_count);
        $this->assertFalse($banner->is_active);

        foreach ([NewsCategory::class, DocumentCategory::class, GalleryAlbum::class, GalleryPhoto::class,
            VillageOfficial::class, Banner::class] as $model) {
            $record = $model::factory()->create(['sort_order' => '3'])->fresh();
            $this->assertSame(3, $record->sort_order);
        }
        $official = VillageOfficial::factory()->create(['is_village_head' => 1, 'is_active' => 0])->fresh();
        $this->assertTrue($official->is_village_head);
        $this->assertFalse($official->is_active);
        $this->assertTrue($agenda->end_at->greaterThanOrEqualTo($agenda->start_at));
    }

    public function test_multiple_non_head_officials_are_allowed(): void
    {
        VillageOfficial::factory()->count(3)->create();
        VillageOfficial::factory()->create(['is_village_head' => true]);

        $this->assertSame(3, VillageOfficial::where('is_village_head', false)->count());
        $this->assertSame(1, VillageOfficial::active()->where('is_village_head', true)->count());
    }
}
