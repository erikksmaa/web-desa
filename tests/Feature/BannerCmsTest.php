<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerCmsTest extends TestCase
{
    use RefreshDatabase;

    private function fields(array $overrides = []): array
    {
        return array_replace(['title' => 'Banner uji', 'subtitle' => 'Informasi', 'is_active' => 1, 'sort_order' => 0], $overrides);
    }

    public function test_authentication_and_forms(): void
    {
        $banner = Banner::factory()->create();
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.banners.'.$action, $action === 'edit' ? $banner : []))->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.banners.store'))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.banners.update', $banner))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.banners.destroy', $banner))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create());
        foreach (['index', 'create', 'edit'] as $action) {
            $this->get(route('admin.banners.'.$action, $action === 'edit' ? $banner : []))->assertOk();
        }
    }

    public function test_required_image_replacement_preservation_and_delete(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.banners.store'), $this->fields())->assertSessionHasErrors('image');
        $this->post(route('admin.banners.store'), $this->fields(['image' => UploadedFile::fake()->image('hero.jpg')]))->assertSessionHasNoErrors();
        $banner = Banner::first();
        $old = $banner->image_path;
        Storage::disk('public')->assertExists($old);
        $this->patch(route('admin.banners.update', $banner), $this->fields(['title' => null]))->assertSessionHasNoErrors();
        $this->assertSame($old, $banner->refresh()->image_path);
        $this->patch(route('admin.banners.update', $banner), $this->fields(['image' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($banner->refresh()->image_path);
        $this->delete(route('admin.banners.destroy', $banner))->assertRedirect(route('admin.banners.index'));
        $this->assertModelMissing($banner);
        Storage::disk('public')->assertMissing($banner->image_path);
    }

    public function test_cta_dates_and_upload_validation(): void
    {
        Storage::fake('public');
        $banner = Banner::factory()->create();
        $this->actingAs(User::factory()->create());
        foreach (['javascript:alert(1)', 'data:text/html,test', '//example.com', 'https://user:pass@example.com'] as $url) {
            $this->patch(route('admin.banners.update', $banner), $this->fields(['cta_url' => $url]))->assertSessionHasErrors('cta_url');
        }
        $this->patch(route('admin.banners.update', $banner), $this->fields(['cta_label' => 'Baca']))->assertSessionHasErrors('cta_url');
        $this->patch(route('admin.banners.update', $banner), $this->fields(['starts_at' => '2026-09-20 10:00', 'ends_at' => '2026-09-20 10:00', 'image' => UploadedFile::fake()->image('bad.png')->size(5121)]))->assertSessionHasErrors(['ends_at', 'image']);
        $this->patch(route('admin.banners.update', $banner), $this->fields(['cta_label' => 'Baca', 'cta_url' => 'https://example.com/news', 'ends_at' => '2026-10-01']))->assertSessionHasNoErrors();
    }

    public function test_active_scope_preserved_and_visibility_boundaries(): void
    {
        $this->travelTo(now()->startOfSecond());
        $visible = Banner::factory()->create(['is_active' => true, 'starts_at' => now(), 'ends_at' => now()->addSecond()]);
        $unscheduled = Banner::factory()->create(['is_active' => true, 'starts_at' => null, 'ends_at' => null]);
        Banner::factory()->create(['is_active' => false]);
        Banner::factory()->create(['is_active' => true, 'starts_at' => now()->addSecond()]);
        Banner::factory()->create(['is_active' => true, 'starts_at' => null, 'ends_at' => now()]);
        $this->assertSame(4, Banner::active()->count());
        $this->assertEqualsCanonicalizing([$visible->id, $unscheduled->id], Banner::currentlyVisible()->pluck('id')->all());
    }

    public function test_search_filter_sort_and_pagination(): void
    {
        $this->actingAs(User::factory()->create());
        Banner::factory()->create(['title' => 'Second special', 'sort_order' => 2, 'is_active' => false]);
        Banner::factory()->create(['title' => 'First special', 'sort_order' => 1, 'is_active' => false]);
        Banner::factory()->count(16)->create(['is_active' => true]);
        $this->get(route('admin.banners.index', ['search' => 'special', 'active' => '0']))->assertSeeInOrder(['First special', 'Second special'])->assertViewHas('banners', fn ($rows) => $rows->total() === 2);
        $this->get(route('admin.banners.index', ['active' => '1']))->assertViewHas('banners', fn ($rows) => $rows->total() === 16 && $rows->count() === 15);
    }

    public function test_failed_update_keeps_old_image_and_cleans_new(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/old.png', 'old');
        $banner = Banner::factory()->create(['image_path' => 'banners/old.png']);
        Banner::updating(fn () => throw new \RuntimeException('Banner failed'));
        $this->actingAs(User::factory()->create())->withoutExceptionHandling();
        try {
            $this->patch(route('admin.banners.update', $banner), $this->fields(['image' => UploadedFile::fake()->image('new.png')]));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Banner failed', $e->getMessage());
        } finally {
            Banner::flushEventListeners();
        }
        $this->assertSame('banners/old.png', $banner->refresh()->image_path);
        $this->assertSame(['banners/old.png'],Storage::disk('public')->allFiles());
    }
}
