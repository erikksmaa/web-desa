<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VillageOfficial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VillageOfficialCmsTest extends TestCase
{
    use RefreshDatabase;

    private function fields(array $overrides = []): array
    {
        return array_replace(['name' => 'Pejabat Uji', 'position' => 'Kepala Desa', 'biography' => 'Biografi', 'is_active' => 1, 'is_village_head' => 0, 'sort_order' => 0], $overrides);
    }

    public function test_all_routes_require_authentication_and_forms_render(): void
    {
        $official = VillageOfficial::factory()->create();
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.village-officials.'.$action, $action === 'edit' ? $official : []))->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.village-officials.store'))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.village-officials.update', $official))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.village-officials.destroy', $official))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create());
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.village-officials.'.$action, $action === 'edit' ? $official : []))->assertOk();
        }
    }

    public function test_head_assignment_replaces_previous_and_inactivation_clears_flag(): void
    {
        $this->actingAs(User::factory()->create());
        $one = VillageOfficial::factory()->create(['is_active' => true, 'is_village_head' => true]);
        $this->post(route('admin.village-officials.store'), $this->fields(['is_village_head' => 1]))->assertSessionHasNoErrors();
        $two = VillageOfficial::latest('id')->first();
        $this->assertFalse($one->refresh()->is_village_head);
        $this->assertTrue($two->is_village_head);
        $this->assertSame(1, VillageOfficial::active()->where('is_village_head', true)->count());
        $this->patch(route('admin.village-officials.update', $one), $this->fields(['is_village_head' => 1]))->assertSessionHasNoErrors();
        $this->assertFalse($two->refresh()->is_village_head);
        $this->patch(route('admin.village-officials.update', $one), $this->fields(['is_village_head' => 1, 'is_active' => 0]))->assertSessionHasNoErrors();
        $this->assertFalse($one->refresh()->is_village_head);
        $this->assertSame(0, VillageOfficial::active()->where('is_village_head', true)->count());
    }

    public function test_photo_replacement_preservation_and_hard_delete(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create())->post(route('admin.village-officials.store'), $this->fields(['photo' => UploadedFile::fake()->image('photo.jpg'), 'photo_path' => 'injected']))->assertSessionHasNoErrors();
        $official = VillageOfficial::first();
        $old = $official->photo_path;
        $this->assertStringStartsWith('officials/', $old);
        Storage::disk('public')->assertExists($old);
        $this->patch(route('admin.village-officials.update', $official), $this->fields())->assertSessionHasNoErrors();
        $this->assertSame($old, $official->refresh()->photo_path);
        $this->patch(route('admin.village-officials.update', $official), $this->fields(['photo' => UploadedFile::fake()->image('replacement.png')]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($official->refresh()->photo_path);
        $this->delete(route('admin.village-officials.destroy', $official))->assertRedirect(route('admin.village-officials.index'));
        $this->assertModelMissing($official);
        Storage::disk('public')->assertMissing($official->photo_path);
    }

    public function test_validation_filter_search_order_and_pagination(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.village-officials.store'), $this->fields(['name' => str_repeat('a', 151), 'sort_order' => -1, 'photo' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]))->assertSessionHasErrors(['name', 'sort_order', 'photo']);
        VillageOfficial::factory()->create(['name' => 'First Uji', 'position' => 'Sekretaris', 'sort_order' => 0, 'is_active' => false]);
        VillageOfficial::factory()->create(['name' => 'Second Uji', 'sort_order' => 1, 'is_active' => false]);
        VillageOfficial::factory()->count(16)->create(['is_active' => true, 'sort_order' => 10]);
        $this->get(route('admin.village-officials.index', ['search' => 'Uji', 'active' => '0']))->assertSeeInOrder(['First Uji', 'Second Uji'])->assertViewHas('officials', fn ($rows) => $rows->total() === 2);
        $this->get(route('admin.village-officials.index', ['search' => 'Sekretaris']))->assertSeeText('First Uji');
        $this->get(route('admin.village-officials.index', ['active' => '1']))->assertViewHas('officials', fn ($rows) => $rows->total() === 16 && $rows->count() === 15);
    }

    public function test_failed_save_restores_head_and_cleans_uploaded_file(): void
    {
        Storage::fake('public');
        $head = VillageOfficial::factory()->create(['is_active' => true, 'is_village_head' => true]);
        VillageOfficial::creating(fn () => throw new \RuntimeException('Official failed'));
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->post(route('admin.village-officials.store'), $this->fields(['is_village_head' => 1, 'photo' => UploadedFile::fake()->image('new.png')]));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Official failed', $e->getMessage());
        } finally {
            VillageOfficial::flushEventListeners();
        }
        $this->assertTrue($head->refresh()->is_village_head);
        $this->assertSame([],Storage::disk('public')->allFiles());
    }
}
