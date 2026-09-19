<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementCmsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace(['title' => 'Informasi Desa', 'content' => "Isi <script>buruk</script>\nBaris kedua", 'status' => 'draft', 'published_at' => null, 'expires_at' => null], $overrides);
    }

    private function pdf(string $name = 'informasi.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    }

    public function test_all_admin_routes_require_authentication(): void
    {
        $item = Announcement::factory()->create();
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.announcements.'.$action, $action === 'edit' ? $item : []))->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.announcements.store'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.announcements.update', $item))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.announcements.destroy', $item))->assertRedirect(route('admin.login'));
    }

    public function test_create_update_author_slug_dates_attachment_and_soft_delete(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        Announcement::factory()->create(['slug' => 'informasi-desa'])->delete();
        $this->actingAs($admin)->get(route('admin.announcements.create'))->assertOk();
        $this->post(route('admin.announcements.store'), $this->payload(['attachment' => $this->pdf(), 'status' => 'published', 'user_id' => 999, 'slug' => 'tampered']))
            ->assertRedirect(route('admin.announcements.index'))->assertSessionHasNoErrors();
        $item = Announcement::where('slug', 'informasi-desa-2')->firstOrFail();
        $this->assertSame($admin->id, $item->user_id);
        $this->assertNotNull($item->published_at);
        $old = $item->attachment_path;
        Storage::disk('local')->assertExists($old);
        $this->get(route('admin.announcements.edit', $item))->assertOk();
        $date = $item->published_at;
        $this->actingAs(User::factory()->create())->put(route('admin.announcements.update', $item), $this->payload(['title' => 'Perubahan', 'attachment' => $this->pdf('baru.pdf'), 'user_id' => 999]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $item->refresh();
        $this->assertSame($admin->id, $item->user_id);
        $this->assertSame('perubahan', $item->slug);
        $this->assertTrue($date->equalTo($item->published_at));
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($item->attachment_path);
        $this->delete(route('admin.announcements.destroy', $item))->assertRedirect();
        $this->assertSoftDeleted($item);
        Storage::disk('local')->assertExists($item->attachment_path);
    }

    public function test_draft_dates_validation_and_disallowed_attachments(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.announcements.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertNull(Announcement::first()->published_at);
        $this->post(route('admin.announcements.store'), $this->payload(['status' => 'published', 'expires_at' => now()->subDay()->toDateTimeString()]))->assertSessionHasErrors('expires_at');
        $this->post(route('admin.announcements.store'), $this->payload(['published_at' => '2026-10-10', 'expires_at' => '2026-10-09']))->assertSessionHasErrors('expires_at');
        foreach ([$this->pdf('bad.php'), UploadedFile::fake()->createWithContent('bad.pdf', '<html>bad</html>')->mimeType('text/html'), UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf')] as $file) {
            $this->post(route('admin.announcements.store'), $this->payload(['attachment' => $file]))->assertSessionHasErrors('attachment');
        }
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_public_visibility_download_headers_missing_files_and_escaped_content(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('announcements/private.pdf', '%PDF-1.4');
        $visible = Announcement::factory()->create(['title' => 'Visible announcement', 'content' => '<script>bad</script>', 'status' => 'published', 'published_at' => now()->subDay(), 'expires_at' => null, 'attachment_path' => 'announcements/private.pdf', 'attachment_original_name' => 'informasi.pdf']);
        $this->get(route('announcements.index'))->assertOk()->assertSeeText($visible->title)->assertDontSee('announcements/private.pdf');
        $this->get(route('announcements.show', $visible->slug))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>bad</script>', false);
        $this->get(route('announcements.download', $visible->slug))->assertOk()->assertDownload('informasi.pdf');
        foreach ([
            ['status' => 'draft'],
            ['published_at' => now()->addDay()],
            ['published_at' => null],
            ['expires_at' => now()],
            ['expires_at' => now()->subDay()],
            ['deleted_at' => now()],
        ] as $changes) {
            $hidden = Announcement::factory()->create(array_replace(['status' => 'published', 'published_at' => now()->subDay(), 'expires_at' => null, 'attachment_path' => 'announcements/private.pdf'], $changes));
            $this->get(route('announcements.show', $hidden->slug))->assertNotFound();
            $this->get(route('announcements.download', $hidden->slug))->assertNotFound();
            $this->get(route('announcements.index'))->assertDontSeeText($hidden->title);
        }
        Storage::disk('local')->delete('announcements/private.pdf');
        $this->get(route('announcements.download', $visible->slug))->assertNotFound();
    }

    public function test_search_status_and_pagination_are_combined(): void
    {
        $this->actingAs(User::factory()->create());
        Announcement::factory()->count(16)->create(['title' => 'Target pengumuman', 'status' => 'draft']);
        $other = Announcement::factory()->create(['title' => 'Target terbit', 'status' => 'published']);
        $this->get(route('admin.announcements.index', ['search' => 'Target', 'status' => 'draft']))
            ->assertOk()->assertDontSeeText($other->title)->assertSee('search=Target', false)->assertSee('status=draft', false)->assertSee('page=2', false);
    }

    public function test_failed_database_update_keeps_old_file_and_cleans_new_upload(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('announcements/old.pdf', 'old');
        $item = Announcement::factory()->create(['attachment_path' => 'announcements/old.pdf']);
        $this->actingAs(User::factory()->create());
        Announcement::updating(fn () => throw new \RuntimeException('Simulated persistence failure'));
        $this->withoutExceptionHandling();
        try {
            $this->put(route('admin.announcements.update', $item), $this->payload(['attachment' => $this->pdf()]));
            $this->fail('Expected persistence failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated persistence failure', $e->getMessage());
        } finally {
            Announcement::flushEventListeners();
        }
        $this->assertSame(['announcements/old.pdf'], Storage::disk('local')->allFiles());
        $this->assertSame('announcements/old.pdf',$item->refresh()->attachment_path);
    }
}
