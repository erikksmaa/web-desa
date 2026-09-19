<?php

namespace Tests\Feature;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryCmsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace(['title' => 'Kegiatan Desa', 'description' => '<script>bad</script>', 'status' => 'draft', 'sort_order' => 0, 'event_date' => '2026-09-01'], $overrides);
    }

    public function test_all_album_and_photo_routes_require_authentication(): void
    {
        $album = GalleryAlbum::factory()->create();
        $photo = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id]);
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.gallery.'.$action, $action === 'edit' ? $album : []))->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.gallery.store'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.gallery.update', $album))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.gallery.destroy', $album))->assertRedirect(route('admin.login'));
        $this->post(route('admin.gallery.photos.store', $album))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.gallery.photos.update', [$album, $photo]))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.gallery.photos.destroy', [$album, $photo]))->assertRedirect(route('admin.login'));
    }

    public function test_album_create_update_cover_author_and_multiple_photos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        GalleryAlbum::factory()->create(['slug' => 'kegiatan-desa']);
        $this->actingAs($admin)->get(route('admin.gallery.create'))->assertOk();
        $this->post(route('admin.gallery.store'), $this->payload([
            'user_id' => 999, 'slug' => 'evil', 'cover' => UploadedFile::fake()->image('cover.jpg'),
            'photos' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.png')],
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $album = GalleryAlbum::where('slug', 'kegiatan-desa-2')->firstOrFail();
        $this->assertSame($admin->id, $album->user_id);
        $this->assertSame(2, $album->photos()->count());
        $this->assertSame([1, 2], $album->photos()->orderBy('sort_order')->pluck('sort_order')->all());
        $old = $album->cover_image;
        Storage::disk('public')->assertExists($old);
        $this->get(route('admin.gallery.edit', $album))->assertOk()->assertSeeText('Foto album');
        $this->actingAs(User::factory()->create())->put(route('admin.gallery.update', $album), $this->payload(['title' => 'Album Baru', 'status' => 'published', 'cover' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasNoErrors()->assertRedirect();
        $album->refresh();
        $this->assertSame($admin->id, $album->user_id);
        $this->assertSame('album-baru', $album->slug);
        $this->assertSame('published', $album->status);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($album->cover_image);
        $this->post(route('admin.gallery.photos.store', $album), ['photos' => [UploadedFile::fake()->image('three.webp')]])->assertSessionHasNoErrors();
        $this->assertSame([1, 2, 3], $album->photos()->orderBy('sort_order')->pluck('sort_order')->all());
    }

    public function test_all_images_are_validated_before_persistence(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.gallery.store'), $this->payload(['photos' => [
            UploadedFile::fake()->image('valid.jpg'), UploadedFile::fake()->create('invalid.svg', 1, 'image/svg+xml'),
        ]]))->assertSessionHasErrors('photos.1');
        $this->post(route('admin.gallery.store'), $this->payload(['cover' => UploadedFile::fake()->image('huge.png')->size(5121)]))->assertSessionHasErrors('cover');
        $photos = [];
        for ($i = 0; $i < 11; $i++) {
            $photos[] = UploadedFile::fake()->image($i.'.jpg');
        }
        $this->post(route('admin.gallery.store'), $this->payload(['photos' => $photos]))->assertSessionHasErrors('photos');
        $this->assertDatabaseCount('gallery_albums', 0);
        $this->assertDatabaseCount('gallery_photos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_photo_metadata_ordering_nested_protection_and_delete_cleanup(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::factory()->create(['status' => 'published', 'cover_image' => 'gallery/photo.jpg']);
        $other = GalleryAlbum::factory()->create();
        Storage::disk('public')->put('gallery/photo.jpg', 'image');
        Storage::disk('public')->put('gallery/second.jpg', 'image');
        $photo = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => 'gallery/photo.jpg', 'sort_order' => 2]);
        $second = GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => 'gallery/second.jpg', 'sort_order' => 1, 'caption' => 'First caption']);
        $this->actingAs(User::factory()->create());
        $metadata = ['caption' => 'Second caption <script>bad</script>', 'alt_text' => 'Kegiatan warga', 'sort_order' => 3, 'image_path' => 'evil', 'gallery_album_id' => $other->id];
        $this->patch(route('admin.gallery.photos.update', [$other, $photo]), ['metadata' => [$photo->id => $metadata]])->assertNotFound();
        $this->delete(route('admin.gallery.photos.destroy', [$other, $photo]))->assertNotFound();
        $this->patch(route('admin.gallery.photos.update', [$album, $photo]), ['metadata' => [$photo->id => $metadata]])->assertSessionHasNoErrors();
        $photo->refresh();
        $this->assertSame($album->id, $photo->gallery_album_id);
        $this->assertSame('gallery/photo.jpg', $photo->image_path);
        $this->assertSame(3, $photo->sort_order);
        $this->get(route('gallery.show', $album->slug))->assertOk()->assertSeeTextInOrder(['First caption', 'Second caption'])->assertSee('alt="Kegiatan warga"', false)->assertSee('&lt;script&gt;', false)->assertDontSee('<script>bad</script>', false);
        $this->delete(route('admin.gallery.photos.destroy', [$album, $photo]))->assertRedirect();
        $this->assertModelMissing($photo);
        Storage::disk('public')->assertMissing('gallery/photo.jpg');
        $this->assertNull($album->refresh()->cover_image);
        $this->assertSame(Storage::disk('public')->url($second->image_path), $album->load('firstPhoto')->coverUrl());
    }

    public function test_album_delete_cascades_rows_and_removes_all_files_after_success(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::factory()->create(['cover_image' => 'gallery/cover.jpg']);
        Storage::disk('public')->put('gallery/cover.jpg', 'image');
        foreach (['gallery/one.jpg', 'gallery/two.jpg'] as $path) {
            Storage::disk('public')->put($path, 'image');
            GalleryPhoto::factory()->create(['gallery_album_id' => $album->id, 'image_path' => $path]);
        }
        $this->actingAs(User::factory()->create())->delete(route('admin.gallery.destroy', $album))->assertRedirect(route('admin.gallery.index'));
        $this->assertModelMissing($album);
        $this->assertDatabaseCount('gallery_photos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_public_draft_visibility_empty_album_and_cover_fallback(): void
    {
        Storage::fake('public');
        $published = GalleryAlbum::factory()->create(['title' => 'Visible album', 'status' => 'published', 'cover_image' => null]);
        $draft = GalleryAlbum::factory()->create(['title' => 'Hidden album', 'status' => 'draft']);
        $this->get(route('gallery.index'))->assertOk()->assertSeeText('Visible album')->assertDontSeeText('Hidden album')->assertSeeText('Galeri Desa');
        $this->get(route('gallery.show', $draft->slug))->assertNotFound();
        $this->get(route('gallery.show', $published->slug))->assertOk()->assertSeeText('Belum ada foto');
        Storage::disk('public')->put('gallery/fallback.jpg', 'image');
        Storage::disk('public')->put('gallery/later.jpg', 'image');
        GalleryPhoto::factory()->create(['gallery_album_id' => $published->id, 'image_path' => 'gallery/later.jpg', 'sort_order' => 2]);
        GalleryPhoto::factory()->create(['gallery_album_id' => $published->id, 'image_path' => 'gallery/fallback.jpg', 'sort_order' => 0]);
        $this->assertSame(Storage::disk('public')->url('gallery/fallback.jpg'), $published->fresh()->load('firstPhoto')->coverUrl());
        $this->get(route('gallery.index'))->assertSee(Storage::disk('public')->url('gallery/fallback.jpg'), false);
    }

    public function test_search_status_filter_and_pagination(): void
    {
        Storage::fake('public');
        GalleryAlbum::factory()->count(16)->create(['title' => 'Target album', 'status' => 'draft']);
        GalleryAlbum::factory()->create(['title' => 'Target excluded', 'status' => 'published']);
        $this->actingAs(User::factory()->create())->get(route('admin.gallery.index', ['search' => 'Target', 'status' => 'draft']))->assertOk()->assertDontSeeText('Target excluded')->assertSee('page=2', false)->assertSee('search=Target', false)->assertSee('status=draft', false);
    }

    public function test_failed_photo_persistence_rolls_back_album_and_all_new_files(): void
    {
        Storage::fake('public');
        $count = 0;
        GalleryPhoto::creating(function () use (&$count): void {
            if (++$count === 2) {
                throw new \RuntimeException('Second photo failed');
            }
        });
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->post(route('admin.gallery.store'), $this->payload(['cover' => UploadedFile::fake()->image('cover.jpg'), 'photos' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')]]));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Second photo failed', $e->getMessage());
        } finally {
            GalleryPhoto::flushEventListeners();
        }
        $this->assertDatabaseCount('gallery_albums', 0);
        $this->assertDatabaseCount('gallery_photos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_album_delete_preserves_rows_and_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery/cover.jpg', 'image');
        $album = GalleryAlbum::factory()->create(['cover_image' => 'gallery/cover.jpg']);
        GalleryAlbum::deleting(fn () => throw new \RuntimeException('Delete failed'));
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->delete(route('admin.gallery.destroy', $album));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Delete failed',$e->getMessage());
        } finally {
            GalleryAlbum::flushEventListeners();
        }
        $this->assertModelExists($album);
        Storage::disk('public')->assertExists('gallery/cover.jpg');
    }
}
