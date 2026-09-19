<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentCmsTest extends TestCase
{
    use RefreshDatabase;

    private function pdf(string $name = 'dokumen.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    }

    private function payload(DocumentCategory $category, array $overrides = []): array
    {
        return array_replace(['title' => 'Peraturan Desa', 'description' => 'Informasi berkas', 'document_category_id' => $category->id, 'status' => 'published', 'published_at' => null], $overrides);
    }

    public function test_all_admin_routes_require_authentication(): void
    {
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['document_category_id' => $category->id]);
        foreach (['documents' => $document, 'document-categories' => $category] as $module => $item) {
            foreach (['index', 'create', 'edit'] as $action) {
                $this->get(route('admin.'.$module.'.'.$action, $action === 'edit' ? $item : []))->assertRedirect(route('admin.login'));
            }
            $this->post(route('admin.'.$module.'.store'))->assertRedirect(route('admin.login'));
            $this->put(route('admin.'.$module.'.update', $item))->assertRedirect(route('admin.login'));
            $this->delete(route('admin.'.$module.'.destroy', $item))->assertRedirect(route('admin.login'));
        }
    }

    public function test_category_crud_unique_slug_and_referenced_delete_protection(): void
    {
        $this->actingAs(User::factory()->create());
        DocumentCategory::factory()->create(['slug' => 'peraturan']);
        $data = ['name' => 'Peraturan', 'description' => 'Aturan desa', 'is_active' => 1, 'sort_order' => 3];
        $this->get(route('admin.document-categories.create'))->assertOk();
        $this->post(route('admin.document-categories.store'), $data)->assertSessionHasNoErrors();
        $category = DocumentCategory::where('slug', 'peraturan-2')->firstOrFail();
        $this->get(route('admin.document-categories.edit', $category))->assertOk();
        $this->put(route('admin.document-categories.update', $category), [...$data, 'name' => 'Peraturan Baru', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->assertSame('peraturan-baru', $category->refresh()->slug);
        $this->get(route('admin.document-categories.index', ['search' => 'Baru']))->assertOk()->assertSeeText('Peraturan Baru');
        Document::factory()->create(['document_category_id' => $category->id])->delete();
        $this->delete(route('admin.document-categories.destroy', $category))->assertSessionHas('error');
        $this->assertModelExists($category);
        $unused = DocumentCategory::factory()->create();
        $this->delete(route('admin.document-categories.destroy', $unused))->assertRedirect();
        $this->assertModelMissing($unused);
    }

    public function test_file_required_validation_active_category_and_metadata(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $category = DocumentCategory::factory()->create();
        $this->actingAs($admin)->get(route('admin.documents.create'))->assertOk();
        $this->post(route('admin.documents.store'), $this->payload($category))->assertSessionHasErrors('file');
        $inactive = DocumentCategory::factory()->create(['is_active' => false]);
        $this->post(route('admin.documents.store'), $this->payload($inactive, ['file' => $this->pdf()]))->assertSessionHasErrors('document_category_id');
        foreach ([$this->pdf('bad.php'), UploadedFile::fake()->create('big.pdf', 15361, 'application/pdf'), UploadedFile::fake()->create('bad.pdf', 1, 'text/html')] as $file) {
            $this->post(route('admin.documents.store'), $this->payload($category, ['file' => $file]))->assertSessionHasErrors('file');
        }
        $file = $this->pdf();
        $this->post(route('admin.documents.store'), $this->payload($category, ['file' => $file, 'user_id' => 999, 'download_count' => 77, 'file_path' => 'evil']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $item = Document::firstOrFail();
        $this->assertSame($admin->id, $item->user_id);
        $this->assertSame(0, $item->download_count);
        $this->assertNotNull($item->published_at);
        $this->assertSame('application/pdf', $item->mime_type);
        $this->assertSame($file->getSize(), $item->file_size);
        $this->assertSame('dokumen.pdf', $item->original_filename);
        $this->assertStringStartsWith('documents/', $item->file_path);
        Storage::disk('local')->assertExists($item->file_path);
    }

    public function test_update_retains_author_date_inactive_category_and_replaces_file_then_soft_deletes(): void
    {
        Storage::fake('local');
        $category = DocumentCategory::factory()->create(['is_active' => false]);
        $author = User::factory()->create();
        Storage::disk('local')->put('documents/old.pdf', 'old');
        $item = Document::factory()->create(['document_category_id' => $category->id, 'user_id' => $author->id, 'status' => 'published', 'published_at' => now()->subDay(), 'file_path' => 'documents/old.pdf']);
        $this->actingAs(User::factory()->create())->get(route('admin.documents.edit', $item))->assertOk()->assertSeeText($category->name);
        $date = $item->published_at;
        $this->put(route('admin.documents.update', $item), $this->payload($category, ['status' => 'draft', 'user_id' => 999]))->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertSame($author->id, $item->user_id);
        $this->assertTrue($date->equalTo($item->published_at));
        $this->assertSame('documents/old.pdf', $item->file_path);
        $this->put(route('admin.documents.update', $item), $this->payload($category, ['file' => $this->pdf('baru.pdf')]))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing('documents/old.pdf');
        $item->refresh();
        Storage::disk('local')->assertExists($item->file_path);
        $this->delete(route('admin.documents.destroy', $item))->assertRedirect();
        $this->assertSoftDeleted($item);
        Storage::disk('local')->assertExists($item->file_path);
    }

    public function test_public_download_checks_visibility_existence_and_counter(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/private.pdf', '%PDF-1.4');
        $category = DocumentCategory::factory()->create(['is_active' => false]);
        $item = Document::factory()->create(['document_category_id' => $category->id, 'title' => 'Dokumen terlihat', 'description' => '<script>bad</script>', 'status' => 'published', 'published_at' => now()->subDay(), 'file_path' => 'documents/private.pdf', 'original_filename' => 'aturan.pdf']);
        $this->get(route('documents.index'))->assertOk()->assertSeeText($item->title)->assertSee('&lt;script&gt;', false)->assertDontSee('documents/private.pdf')->assertDontSee('<script>bad</script>', false);
        $this->get(route('documents.download', $item))->assertOk()->assertDownload('aturan.pdf');
        $this->assertSame(1, $item->refresh()->download_count);
        foreach ([['status' => 'draft'], ['published_at' => now()->addDay()], ['published_at' => null], ['deleted_at' => now()]] as $changes) {
            $hidden = Document::factory()->create(array_replace(['status' => 'published', 'published_at' => now()->subDay(), 'file_path' => 'documents/private.pdf'], $changes));
            $this->get(route('documents.download', $hidden))->assertNotFound();
            $this->get(route('documents.index'))->assertDontSeeText($hidden->title);
            $this->assertSame(0, $hidden->refresh()->download_count);
        }
        Storage::disk('local')->delete('documents/private.pdf');
        $this->get(route('documents.download', $item))->assertNotFound();
        $this->assertSame(1, $item->refresh()->download_count);
    }

    public function test_search_filters_and_pagination_for_admin_and_public(): void
    {
        $category = DocumentCategory::factory()->create();
        Document::factory()->count(16)->create(['document_category_id' => $category->id, 'title' => 'Target berkas', 'status' => 'published', 'published_at' => now()->subDay()]);
        $other = Document::factory()->create(['title' => 'Target kategori lain', 'status' => 'published', 'published_at' => now()->subDay()]);
        $draft = Document::factory()->create(['document_category_id' => $category->id, 'title' => 'Target draf', 'status' => 'draft']);
        $params = ['search' => 'Target', 'category' => $category->id, 'status' => 'published'];
        $this->actingAs(User::factory()->create())->get(route('admin.documents.index', $params))->assertOk()->assertDontSeeText($other->title)->assertDontSeeText($draft->title)->assertSee('page=2', false)->assertSee('search=Target', false)->assertSee('category='.$category->id, false);
        $this->get(route('documents.index', $params))->assertOk()->assertDontSeeText($other->title)->assertDontSeeText($draft->title)->assertSee('page=2', false)->assertSee('search=Target', false);
    }

    public function test_database_failure_does_not_lose_original_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/old.pdf', 'old');
        $category = DocumentCategory::factory()->create();
        $item = Document::factory()->create(['document_category_id' => $category->id, 'file_path' => 'documents/old.pdf']);
        Document::updating(fn () => throw new \RuntimeException('DB failure'));
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->put(route('admin.documents.update', $item), $this->payload($category, ['file' => $this->pdf()]));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('DB failure', $e->getMessage());
        } finally {
            Document::flushEventListeners();
        }
        $this->assertSame(['documents/old.pdf'],Storage::disk('local')->allFiles());
        $this->assertSame('documents/old.pdf',$item->refresh()->file_path);
    }
}
