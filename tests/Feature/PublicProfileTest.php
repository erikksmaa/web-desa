<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\VillageOfficial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_renders_plain_text_and_empty_settings(): void
    {
        $this->get(route('profile.index'))->assertOk()->assertSeeText('Informasi visi belum tersedia.');
        foreach (['vision', 'mission', 'history'] as $key) {
            Setting::create(['key' => 'village.'.$key, 'value' => $key." <script>alert(1)</script>\nBaris kedua", 'type' => 'text', 'group' => 'village']);
        }
        $this->get(route('profile.index'))->assertOk()->assertSee('vision')->assertSee('mission')->assertSee('history')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('Baris kedua');
    }

    public function test_sotk_handles_missing_and_existing_image(): void
    {
        Storage::fake('public');
        $this->get(route('profile.organization'))->assertOk()->assertSeeText('Struktur organisasi belum tersedia.');
        Setting::create(['key' => 'sotk.image', 'value' => 'settings/sotk/chart.png', 'type' => 'string', 'group' => 'sotk']);
        $this->get(route('profile.organization'))->assertOk()->assertSeeText('Struktur organisasi belum tersedia.');
        Storage::disk('public')->put('settings/sotk/chart.png', 'image');
        $this->get(route('profile.organization'))->assertOk()->assertSee(Storage::disk('public')->url('settings/sotk/chart.png'))->assertSeeText('Lihat nama dan jabatan perangkat desa');
    }

    public function test_only_active_officials_render_with_order_and_head_indicator(): void
    {
        Storage::fake('public');
        $this->get(route('profile.officials'))->assertOk()->assertSeeText('Informasi perangkat desa belum tersedia.');
        VillageOfficial::factory()->create(['name' => 'Second Official', 'sort_order' => 2, 'is_active' => true]);
        VillageOfficial::factory()->create(['name' => 'First Head', 'sort_order' => 1, 'is_active' => true, 'is_village_head' => true, 'photo_path' => 'missing.jpg', 'biography' => '<script>bad()</script>']);
        VillageOfficial::factory()->create(['name' => 'Hidden Official', 'is_active' => false]);
        $this->get(route('profile.officials'))->assertOk()->assertSeeInOrder(['First Head', 'Second Official'])->assertSeeText('Kepala Desa')->assertDontSee('Hidden Official')->assertSeeText('Foto belum tersedia')->assertDontSee('<script>bad()</script>', false)->assertViewHas('officials', fn ($rows) => $rows->total() === 2);
    }
}
