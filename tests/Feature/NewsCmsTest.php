<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_and_category_admin_routes_require_authentication(): void
    {
        $category = NewsCategory::factory()->create();
        $news = News::factory()->create(['news_category_id' => $category->id]);

        foreach ([
            ['get', route('admin.news.index')],
            ['get', route('admin.news.create')],
            ['post', route('admin.news.store')],
            ['get', route('admin.news.edit', $news)],
            ['put', route('admin.news.update', $news)],
            ['delete', route('admin.news.destroy', $news)],
            ['get', route('admin.news-categories.index')],
            ['get', route('admin.news-categories.create')],
            ['post', route('admin.news-categories.store')],
            ['get', route('admin.news-categories.edit', $category)],
            ['put', route('admin.news-categories.update', $category)],
            ['delete', route('admin.news-categories.destroy', $category)],
        ] as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_can_create_category_with_unique_generated_slug(): void
    {
        NewsCategory::factory()->create(['slug' => 'layanan-publik']);
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.news-categories.store'), [
            'name' => '  Layanan Publik  ',
            'description' => '  Informasi layanan.  ',
            'is_active' => '1',
            'sort_order' => '2',
        ])->assertRedirect(route('admin.news-categories.index'))
            ->assertSessionHas('success', 'Kategori berhasil ditambahkan.');

        $this->assertDatabaseHas('news_categories', [
            'name' => 'Layanan Publik',
            'slug' => 'layanan-publik-2',
            'description' => 'Informasi layanan.',
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_category_update_regenerates_slug_only_when_name_changes(): void
    {
        $admin = User::factory()->create();
        $category = NewsCategory::factory()->create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);

        $this->actingAs($admin)->put(route('admin.news-categories.update', $category), [
            'name' => 'Kegiatan Warga',
            'description' => '',
            'is_active' => '0',
            'sort_order' => '3',
        ])->assertRedirect(route('admin.news-categories.index'));

        $this->assertDatabaseHas('news_categories', [
            'id' => $category->id,
            'name' => 'Kegiatan Warga',
            'slug' => 'kegiatan-warga',
            'is_active' => false,
        ]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $category = NewsCategory::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.news-categories.destroy', $category))
            ->assertRedirect(route('admin.news-categories.index'))
            ->assertSessionHas('success', 'Kategori berhasil dihapus.');

        $this->assertDatabaseMissing('news_categories', ['id' => $category->id]);
    }

    public function test_category_delete_is_blocked_when_active_or_trashed_news_references_it(): void
    {
        $admin = User::factory()->create();
        $category = NewsCategory::factory()->create();
        $news = News::factory()->create(['news_category_id' => $category->id]);
        $news->delete();

        $this->actingAs($admin)
            ->from(route('admin.news-categories.index'))
            ->delete(route('admin.news-categories.destroy', $category))
            ->assertRedirect(route('admin.news-categories.index'))
            ->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh berita.');

        $this->assertDatabaseHas('news_categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_published_news_with_author_slug_timestamp_and_thumbnail(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $category = NewsCategory::factory()->create();
        News::factory()->create(['title' => 'Musyawarah Desa', 'slug' => 'musyawarah-desa']);
        $trashed = News::factory()->create(['title' => 'Lama', 'slug' => 'musyawarah-desa-2']);
        $trashed->delete();

        $this->freezeTime(function () use ($admin, $category): void {
            $this->actingAs($admin)->post(route('admin.news.store'), [
                ...$this->newsPayload($category),
                'title' => 'Musyawarah Desa',
                'status' => News::STATUS_PUBLISHED,
                'published_at' => '',
                'thumbnail' => UploadedFile::fake()->image('foto desa.jpg', 1200, 675),
            ])->assertRedirect(route('admin.news.index'))
                ->assertSessionHas('success', 'Berita berhasil ditambahkan.');

            $news = News::where('slug', 'musyawarah-desa-3')->firstOrFail();
            $this->assertSame($admin->id, $news->user_id);
            $this->assertSame(now()->format('Y-m-d H:i:s'), $news->published_at->format('Y-m-d H:i:s'));
            $this->assertStringStartsWith('news/thumbnails/', $news->thumbnail);
            Storage::disk('public')->assertExists($news->thumbnail);
        });
    }

    public function test_draft_creation_keeps_publication_date_nullable(): void
    {
        $category = NewsCategory::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('admin.news.store'), [
            ...$this->newsPayload($category),
            'status' => News::STATUS_DRAFT,
            'published_at' => '',
        ])->assertRedirect(route('admin.news.index'));

        $this->assertNull(News::latest('id')->firstOrFail()->published_at);
    }

    public function test_update_preserves_author_and_timestamp_and_regenerates_slug(): void
    {
        $author = User::factory()->create();
        $editor = User::factory()->create();
        $category = NewsCategory::factory()->create(['is_active' => false]);
        $publishedAt = now()->subWeek()->startOfSecond();
        $news = News::factory()->create([
            'news_category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => $publishedAt,
        ]);

        $this->actingAs($editor)->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSeeText($category->name);

        $this->actingAs($editor)->put(route('admin.news.update', $news), [
            ...$this->newsPayload($category),
            'title' => 'Judul Baru',
            'status' => News::STATUS_DRAFT,
            'published_at' => '',
        ])->assertRedirect(route('admin.news.index'));

        $news->refresh();
        $this->assertSame($author->id, $news->user_id);
        $this->assertSame('judul-baru', $news->slug);
        $this->assertSame(News::STATUS_DRAFT, $news->status);
        $this->assertTrue($news->published_at->equalTo($publishedAt));
    }

    public function test_thumbnail_replacement_removes_old_file_after_success(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('news/thumbnails/old.jpg', 'old');
        $category = NewsCategory::factory()->create();
        $news = News::factory()->create([
            'news_category_id' => $category->id,
            'thumbnail' => 'news/thumbnails/old.jpg',
        ]);

        $this->actingAs(User::factory()->create())->put(route('admin.news.update', $news), [
            ...$this->newsPayload($category),
            'thumbnail' => UploadedFile::fake()->image('new.webp', 800, 450),
        ])->assertRedirect(route('admin.news.index'));

        $news->refresh();
        Storage::disk('public')->assertExists($news->thumbnail);
        Storage::disk('public')->assertMissing('news/thumbnails/old.jpg');
    }

    public function test_soft_delete_retains_thumbnail_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('news/thumbnails/retained.jpg', 'image');
        $news = News::factory()->create(['thumbnail' => 'news/thumbnails/retained.jpg']);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.news.destroy', $news))
            ->assertRedirect(route('admin.news.index'))
            ->assertSessionHas('success', 'Berita berhasil dihapus.');

        $this->assertSoftDeleted($news);
        Storage::disk('public')->assertExists('news/thumbnails/retained.jpg');
    }

    public function test_invalid_thumbnail_is_rejected_without_writing_a_file(): void
    {
        Storage::fake('public');
        $category = NewsCategory::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('admin.news.store'), [
            ...$this->newsPayload($category),
            'thumbnail' => UploadedFile::fake()->create('script.svg', 20, 'image/svg+xml'),
        ])->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseCount('news', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_admin_listing_supports_combined_search_status_and_category_filters(): void
    {
        $wantedCategory = NewsCategory::factory()->create(['name' => 'Pembangunan']);
        $otherCategory = NewsCategory::factory()->create(['name' => 'Lainnya']);
        News::factory()->create([
            'news_category_id' => $wantedCategory->id,
            'title' => 'Jalan Desa Diperbaiki',
            'status' => News::STATUS_PUBLISHED,
        ]);
        News::factory()->create([
            'news_category_id' => $wantedCategory->id,
            'title' => 'Jalan Desa Masih Rencana',
            'status' => News::STATUS_DRAFT,
        ]);
        News::factory()->create([
            'news_category_id' => $otherCategory->id,
            'title' => 'Jalan Kabupaten',
            'status' => News::STATUS_PUBLISHED,
        ]);

        $this->actingAs(User::factory()->create())->get(route('admin.news.index', [
            'search' => 'Jalan',
            'status' => News::STATUS_PUBLISHED,
            'category' => $wantedCategory->id,
        ]))->assertOk()
            ->assertSeeText('Jalan Desa Diperbaiki')
            ->assertDontSeeText('Jalan Desa Masih Rencana')
            ->assertDontSeeText('Jalan Kabupaten');
    }

    public function test_admin_pagination_preserves_query_parameters(): void
    {
        $category = NewsCategory::factory()->create();
        News::factory()->count(16)->create([
            'news_category_id' => $category->id,
            'status' => News::STATUS_DRAFT,
            'excerpt' => 'Target pencarian',
        ]);

        $this->actingAs(User::factory()->create())->get(route('admin.news.index', [
            'search' => 'Target',
            'status' => 'draft',
            'category' => $category->id,
        ]))->assertOk()
            ->assertSee('search=Target', false)
            ->assertSee('status=draft', false)
            ->assertSee('category='.$category->id, false)
            ->assertSee('page=2', false);
    }

    public function test_public_index_only_shows_currently_published_news(): void
    {
        $category = NewsCategory::factory()->create();
        $published = News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Terbit',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);
        News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Draf',
            'status' => News::STATUS_DRAFT,
        ]);
        News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Masa Depan',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
        ]);
        $deleted = News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Dihapus',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
        $deleted->delete();

        $this->get(route('news.index'))->assertOk()
            ->assertSeeText($published->title)
            ->assertDontSeeText('Berita Draf')
            ->assertDontSeeText('Berita Masa Depan')
            ->assertDontSeeText('Berita Dihapus')
            ->assertSee('aria-current="page"', false);
    }

    public function test_published_detail_increments_views_and_escapes_plain_text_content(): void
    {
        $news = News::factory()->create([
            'content' => "<script>alert('x')</script>\nParagraf kedua",
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'views' => 4,
        ]);

        $this->get(route('news.show', $news->slug))->assertOk()
            ->assertSeeText($news->title)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertSeeText('5 kali dilihat');

        $this->assertSame(5, $news->refresh()->views);
    }

    public function test_unpublished_future_and_soft_deleted_details_return_404_without_incrementing(): void
    {
        $draft = News::factory()->create(['status' => News::STATUS_DRAFT, 'views' => 1]);
        $future = News::factory()->create([
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
            'views' => 2,
        ]);
        $deleted = News::factory()->create([
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'views' => 3,
        ]);
        $deleted->delete();

        $this->get(route('news.show', $draft->slug))->assertNotFound();
        $this->get(route('news.show', $future->slug))->assertNotFound();
        $this->get(route('news.show', $deleted->slug))->assertNotFound();

        $this->assertSame(1, $draft->refresh()->views);
        $this->assertSame(2, $future->refresh()->views);
        $this->assertSame(3, $deleted->refresh()->views);
    }

    public function test_related_news_contains_only_other_published_news_in_same_category(): void
    {
        $category = NewsCategory::factory()->create();
        $otherCategory = NewsCategory::factory()->create();
        $current = News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Utama',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);
        $related = News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Terkait Terbit',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
        News::factory()->create([
            'news_category_id' => $category->id,
            'title' => 'Berita Terkait Draf',
            'status' => News::STATUS_DRAFT,
        ]);
        News::factory()->create([
            'news_category_id' => $otherCategory->id,
            'title' => 'Berita Kategori Lain',
            'status' => News::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('news.show', $current->slug))->assertOk()
            ->assertSeeText($related->title)
            ->assertDontSeeText('Berita Terkait Draf')
            ->assertDontSeeText('Berita Kategori Lain');
    }

    private function newsPayload(NewsCategory $category): array
    {
        return [
            'title' => 'Informasi Pelayanan Desa',
            'news_category_id' => $category->id,
            'excerpt' => 'Ringkasan berita.',
            'content' => "Isi berita baris pertama.\nBaris kedua.",
            'status' => News::STATUS_DRAFT,
            'published_at' => '',
        ];
    }
}
