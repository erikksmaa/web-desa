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
use App\Models\Setting;
use App\Models\User;
use App\Models\VillageOfficial;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CoreSchemaTest extends TestCase
{
    use RefreshDatabase;

    public static function slugModels(): array
    {
        return array_map(fn ($model) => [$model], [
            NewsCategory::class, News::class, Announcement::class, Agenda::class,
            DocumentCategory::class, GalleryAlbum::class,
        ]);
    }

    #[DataProvider('slugModels')]
    public function test_database_rejects_duplicate_slugs(string $model): void
    {
        $model::factory()->create(['slug' => 'same-slug']);

        $this->expectException(QueryException::class);
        $model::factory()->create(['slug' => 'same-slug']);
    }

    public function test_setting_key_is_unique(): void
    {
        Setting::create(['key' => 'site.name', 'value' => 'Website Desa']);

        $this->expectException(QueryException::class);
        Setting::create(['key' => 'site.name', 'value' => 'Duplicate']);
    }

    public static function categories(): array
    {
        return [
            [NewsCategory::class, News::class, 'news_category_id', 'news'],
            [DocumentCategory::class, Document::class, 'document_category_id', 'documents'],
        ];
    }

    #[DataProvider('categories')]
    public function test_referenced_categories_cannot_be_deleted_even_after_content_is_trashed(
        string $categoryModel, string $contentModel, string $foreignKey, string $relation
    ): void {
        $category = $categoryModel::factory()->create();
        $content = $contentModel::factory()->create([$foreignKey => $category->id]);

        $this->assertTrue($content->category->is($category));
        $this->assertTrue($category->$relation->sole()->is($content));
        $content->delete();

        $this->expectException(QueryException::class);
        $category->delete();
    }

    #[DataProvider('categories')]
    public function test_unreferenced_category_can_be_deleted(
        string $categoryModel, string $contentModel, string $foreignKey, string $relation
    ): void {
        $category = $categoryModel::factory()->create();
        $content = $contentModel::factory()->create([$foreignKey => $category->id]);
        $content->forceDelete();
        $category->delete();

        $this->assertDatabaseMissing($category->getTable(), ['id' => $category->id]);
    }

    public static function authoredModels(): array
    {
        return [
            [News::class, 'news'], [Announcement::class, 'announcements'],
            [Agenda::class, 'agendas'], [Document::class, 'documents'],
            [GalleryAlbum::class, 'galleryAlbums'],
        ];
    }

    #[DataProvider('authoredModels')]
    public function test_deleting_author_preserves_content_and_nulls_foreign_key(string $model, string $relation): void
    {
        $author = User::factory()->create();
        $content = $model::factory()->create(['user_id' => $author->id]);
        $this->assertTrue($content->author->is($author));
        $this->assertTrue($author->$relation->sole()->is($content));

        $author->delete();

        $this->assertNull($content->fresh()->user_id);
        $this->assertNull($content->fresh()->author);
        $this->assertDatabaseHas($content->getTable(), ['id' => $content->id]);
    }

    public static function foreignKeys(): array
    {
        return [
            [News::class, 'news_category_id'], [Document::class, 'document_category_id'],
            [GalleryPhoto::class, 'gallery_album_id'],
            [News::class, 'user_id'], [Announcement::class, 'user_id'],
            [Agenda::class, 'user_id'], [Document::class, 'user_id'],
            [GalleryAlbum::class, 'user_id'],
        ];
    }

    #[DataProvider('foreignKeys')]
    public function test_orphan_references_are_rejected(string $model, string $foreignKey): void
    {
        $this->expectException(QueryException::class);
        $model::factory()->create([$foreignKey => 999999]);
    }

    public function test_album_deletion_cascades_photo_rows_only(): void
    {
        $album = GalleryAlbum::factory()->create();
        $photo = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id]);
        $otherPhoto = GalleryPhoto::factory()->create();

        $this->assertTrue($photo->album->is($album));
        $this->assertTrue($album->photos->sole()->is($photo));
        $album->delete();

        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('gallery_photos', ['id' => $otherPhoto->id]);
    }

    public static function softDeletedModels(): array
    {
        return [[News::class], [Announcement::class], [Agenda::class], [Document::class]];
    }

    #[DataProvider('softDeletedModels')]
    public function test_content_can_be_soft_deleted_and_restored(string $model): void
    {
        $content = $model::factory()->create();
        $content->delete();

        $this->assertSoftDeleted($content);
        $this->assertNull($model::find($content->id));
        $this->assertNotNull($model::withTrashed()->find($content->id));

        $content->restore();
        $this->assertNotNull($model::find($content->id));
    }

    public function test_trashed_news_reserves_its_slug_for_safe_restoration(): void
    {
        $news = News::factory()->create(['slug' => 'reserved-slug']);
        $news->delete();

        $this->expectException(QueryException::class);
        News::factory()->create(['slug' => 'reserved-slug']);
    }

    public function test_database_defaults_are_safe_without_factory_defaults(): void
    {
        $category = NewsCategory::create(['name' => 'Contoh', 'slug' => 'contoh'])->refresh();
        $this->assertTrue($category->is_active);
        $this->assertSame(0, $category->sort_order);

        $news = News::create([
            'news_category_id' => $category->id, 'title' => 'Contoh',
            'slug' => 'contoh', 'content' => 'Isi contoh',
        ])->refresh();
        $this->assertSame(News::STATUS_DRAFT, $news->status);
        $this->assertSame(0, $news->views);
        $this->assertNull($news->user_id);
        $this->assertNull($news->published_at);

        $documentCategory = DocumentCategory::create(['name' => 'Berkas', 'slug' => 'berkas'])->refresh();
        $document = Document::create([
            'document_category_id' => $documentCategory->id,
            'title' => 'Contoh', 'file_path' => 'development/placeholders/test.pdf',
            'original_filename' => 'test.pdf',
        ])->refresh();
        $this->assertTrue($documentCategory->is_active);
        $this->assertSame(Document::STATUS_DRAFT, $document->status);
        $this->assertSame(0, $document->download_count);
        $this->assertNull($document->file_size);

        $announcement = Announcement::create(['title' => 'Contoh', 'slug' => 'contoh', 'content' => 'Contoh'])->refresh();
        $agenda = Agenda::create(['title' => 'Contoh', 'slug' => 'contoh', 'start_at' => now()])->refresh();
        $album = GalleryAlbum::create(['title' => 'Contoh', 'slug' => 'contoh'])->refresh();
        foreach ([$announcement, $agenda, $album] as $item) {
            $this->assertSame('draft', $item->status);
            $this->assertNull($item->user_id);
        }
        $photo = GalleryPhoto::create(['gallery_album_id' => $album->id, 'image_path' => 'development/placeholders/test.jpg'])->refresh();
        $this->assertSame(0, $photo->sort_order);

        $official = VillageOfficial::create(['name' => 'Contoh', 'position' => 'Staf'])->refresh();
        $this->assertTrue($official->is_active);
        $this->assertFalse($official->is_village_head);
        $banner = Banner::create(['image_path' => 'development/placeholders/banner.jpg'])->refresh();
        $this->assertTrue($banner->is_active);
        $this->assertSame(0, $banner->sort_order);

        $setting = Setting::create(['key' => 'site.name'])->refresh();
        $this->assertSame(Setting::TYPE_STRING, $setting->type);
        $this->assertSame('general', $setting->group);
        $this->assertNull($setting->value);
    }

    public function test_fillable_does_not_allow_primary_key_or_deleted_at_assignment(): void
    {
        foreach ([NewsCategory::class, News::class, Announcement::class, Agenda::class,
            DocumentCategory::class, Document::class, GalleryAlbum::class, GalleryPhoto::class,
            VillageOfficial::class, Banner::class, Setting::class] as $model) {
            $instance = new $model;
            $this->assertFalse($instance->isFillable('id'));
            $this->assertFalse($instance->isFillable('deleted_at'));
            $this->assertFalse($instance->isFillable('created_at'));
        }
    }
}
