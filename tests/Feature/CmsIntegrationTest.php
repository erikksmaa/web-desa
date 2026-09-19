<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_new_empty_listings_render_and_navigation_links_work(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['announcements', 'agendas', 'documents', 'document-categories', 'gallery'] as $module) {
            $this->get(route('admin.'.$module.'.index'))->assertOk()->assertDontSee('href="#"', false);
            $this->get(route('admin.'.$module.'.create'))->assertOk()->assertSee('name="_token"', false);
        }
        foreach (['announcements', 'agendas', 'documents', 'gallery'] as $module) {
            $response = $this->get(route($module.'.index'))->assertOk()->assertDontSee('href="#"', false);
            $this->assertMatchesRegularExpression('/href="'.preg_quote(route($module.'.index'), '/').'"\s+aria-current="page"/', $response->getContent());
        }
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('href="'.route('admin.announcements.index').'"', false);
        $this->get(route('admin.news.index'))->assertOk()->assertSee('href="'.route('admin.news.index').'"', false);
    }

    public function test_dashboard_still_handles_managed_content(): void
    {
        Announcement::factory()->create(['title' => 'Managed announcement']);
        Agenda::factory()->create(['title' => 'Managed agenda', 'status' => 'published', 'start_at' => now()->addDay()]);
        Document::factory()->create(['title' => 'Managed document']);
        GalleryAlbum::factory()->create(['title' => 'Managed album']);
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertOk()->assertSeeText('Managed announcement')->assertSeeText('Managed agenda')->assertSeeText('Berkas')->assertSeeText('Album Galeri');
    }

    public function test_unique_slug_fits_existing_schema_and_handles_empty_transliteration(): void
    {
        $title = str_repeat('long-title-', 25);
        $slug = UniqueSlug::generate(News::withTrashed(), $title);
        $this->assertLessThanOrEqual(190, strlen($slug));
        News::factory()->create(['slug' => $slug])->delete();
        $next = UniqueSlug::generate(News::withTrashed(), $title);
        $this->assertSame($slug.'-2', $next);
        $this->assertLessThanOrEqual(190, strlen($next));
        $this->assertSame('item', UniqueSlug::generate(News::withTrashed(), '!!!'));
    }

    public function test_photo_validation_old_input_is_scoped_to_one_photo(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::factory()->create(['sort_order' => 7]);
        $one = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'caption' => 'One']);
        $two = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'caption' => 'Two']);
        $this->actingAs(User::factory()->create())
            ->from(route('admin.gallery.edit', $album))
            ->patch(route('admin.gallery.photos.update', [$album, $one]), ['metadata' => [$one->id => ['caption' => 'Edited one', 'alt_text' => '', 'sort_order' => -1]]])
            ->assertSessionHasErrors('metadata.'.$one->id.'.sort_order');
        $this->get(route('admin.gallery.edit', $album))->assertOk()->assertSee('value="Edited one"', false)->assertSee('value="Two"', false)->assertSee('value="7"', false);
        $this->assertSame('Two', $two->refresh()->caption);
    }

    public function test_failed_photo_batch_for_existing_album_rolls_back_only_new_data(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::factory()->create();
        Storage::disk('public')->put('gallery/original.jpg', 'old');
        $original = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => 'gallery/original.jpg']);
        $count = 0;
        GalleryPhoto::creating(function () use (&$count): void {
            if (++$count === 2) {
                throw new \RuntimeException('Batch failed');
            }
        });
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->post(route('admin.gallery.photos.store', $album), ['photos' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')]]);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Batch failed', $e->getMessage());
        } finally {
            GalleryPhoto::flushEventListeners();
        }
        $this->assertModelExists($album);
        $this->assertModelExists($original);
        $this->assertSame(1, $album->photos()->count());
        $this->assertSame(['gallery/original.jpg'], Storage::disk('public')->allFiles());
    }
}
