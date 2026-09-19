<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use App\Models\VillageOfficial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileFileLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_hard_deletes_preserve_database_rows_and_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        foreach ([Banner::class => ['banners', 'image_path'], VillageOfficial::class => ['village-officials', 'photo_path']] as $model => [$module,$field]) {
            $path = $module.'/original.png';
            Storage::disk('public')->put($path, 'original');
            $record = $model::factory()->create([$field => $path]);
            $model::deleting(fn () => throw new \RuntimeException('Delete failed'));
            try {
                $this->delete(route('admin.'.$module.'.destroy', $record));
                $this->fail('Expected failure');
            } catch (\RuntimeException $e) {
                $this->assertSame('Delete failed', $e->getMessage());
            } finally {
                $model::flushEventListeners();
            }
            $this->assertModelExists($record);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_failed_official_replacement_retains_old_photo_and_head(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('officials/old.png', 'original');
        $record = VillageOfficial::factory()->create(['photo_path' => 'officials/old.png', 'is_village_head' => true, 'is_active' => true]);
        VillageOfficial::updating(fn () => throw new \RuntimeException('Update failed'));
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->patch(route('admin.village-officials.update', $record), ['name' => 'Updated', 'position' => 'Kepala Desa', 'is_active' => 1, 'is_village_head' => 1, 'sort_order' => 0, 'photo' => UploadedFile::fake()->image('new.png')]);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Update failed', $e->getMessage());
        } finally {
            VillageOfficial::flushEventListeners();
        }
        $this->assertSame('officials/old.png', $record->refresh()->photo_path);
        $this->assertTrue($record->is_village_head);
        $this->assertSame(['officials/old.png'], Storage::disk('public')->allFiles());
    }

    public function test_official_upload_rejects_large_or_disguised_files(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $data = ['name' => 'Official', 'position' => 'Sekretaris', 'is_active' => 1, 'is_village_head' => 0, 'sort_order' => 0];
        foreach ([UploadedFile::fake()->image('large.jpg')->size(5121), UploadedFile::fake()->createWithContent('fake.jpg', '<script>bad()</script>')->mimeType('text/html')] as $photo) {
            $this->post(route('admin.village-officials.store'), [...$data, 'photo' => $photo])->assertSessionHasErrors('photo');
        }
        $this->assertDatabaseCount('village_officials', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
